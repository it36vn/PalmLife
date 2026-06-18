<?php

namespace App\Services;

use App\Models\StorePurchase;
use App\Models\StoreServerNotification;
use App\Models\SubscriptionPlan;

class StoreWebhookService
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
        private readonly UserNotificationService $notifications,
    ) {}

    public function handleApple(array $payload): StoreServerNotification
    {
        $decoded = $this->decodeJwsPayload($payload['signedPayload'] ?? '');
        $data = $decoded['data'] ?? [];
        $transaction = $this->decodeJwsPayload($data['signedTransactionInfo'] ?? '');
        $productId = $transaction['productId'] ?? null;
        $transactionId = $transaction['transactionId'] ?? $transaction['originalTransactionId'] ?? null;
        $eventId = $decoded['notificationUUID'] ?? $transactionId;
        $eventType = $decoded['notificationType'] ?? 'UNKNOWN';
        $status = $this->appleStatus($eventType, $decoded['subtype'] ?? null);

        $notification = StoreServerNotification::updateOrCreate(
            ['platform' => 'ios', 'event_id' => $eventId],
            [
                'event_type' => $eventType,
                'product_id' => $productId,
                'transaction_id' => $transactionId,
                'status' => $status,
                'payload' => ['raw' => $payload, 'decoded' => $decoded, 'transaction' => $transaction],
                'processed_at' => now(),
            ],
        );

        $this->applyStoreStatus('ios', $productId, null, $transactionId, $status, $notification->payload);

        return $notification;
    }

    public function handleGoogle(array $payload): StoreServerNotification
    {
        $message = $payload['message'] ?? [];
        $decoded = [];
        if (isset($message['data'])) {
            $decoded = json_decode(base64_decode($message['data'], true) ?: '{}', true) ?: [];
        } elseif (isset($payload['subscriptionNotification']) || isset($payload['oneTimeProductNotification'])) {
            $decoded = $payload;
        }

        $subscription = $decoded['subscriptionNotification'] ?? null;
        $oneTime = $decoded['oneTimeProductNotification'] ?? null;
        $purchaseToken = $subscription['purchaseToken'] ?? $oneTime['purchaseToken'] ?? null;
        $productId = $oneTime['sku'] ?? null;
        $eventType = $subscription
            ? 'SUBSCRIPTION_'.$subscription['notificationType']
            : ($oneTime ? 'ONE_TIME_'.$oneTime['notificationType'] : 'UNKNOWN');
        $status = $subscription
            ? $this->googleSubscriptionStatus((int) $subscription['notificationType'])
            : $this->googleOneTimeStatus((int) ($oneTime['notificationType'] ?? 0));

        if ($productId === null && $purchaseToken !== null) {
            $productId = StorePurchase::where('platform', 'android')
                ->where('purchase_token', $purchaseToken)
                ->latest()
                ->value('product_id');
        }

        $eventId = $message['messageId'] ?? hash('sha256', json_encode($decoded));
        $notification = StoreServerNotification::updateOrCreate(
            ['platform' => 'android', 'event_id' => $eventId],
            [
                'event_type' => $eventType,
                'product_id' => $productId,
                'purchase_token' => $purchaseToken,
                'status' => $status,
                'payload' => ['raw' => $payload, 'decoded' => $decoded],
                'processed_at' => now(),
            ],
        );

        $this->applyStoreStatus('android', $productId, $purchaseToken, null, $status, $notification->payload);

        return $notification;
    }

    private function applyStoreStatus(string $platform, ?string $productId, ?string $purchaseToken, ?string $transactionId, string $status, array $payload): void
    {
        $purchase = StorePurchase::query()
            ->where('platform', $platform)
            ->where(function ($query) use ($purchaseToken, $transactionId): void {
                if ($purchaseToken) {
                    $query->where('purchase_token', $purchaseToken);
                }
                if ($transactionId) {
                    $method = $purchaseToken ? 'orWhere' : 'where';
                    $query->{$method}('transaction_id', $transactionId);
                }
            })
            ->latest()
            ->first();

        if (! $purchase && $productId) {
            $purchase = StorePurchase::where('platform', $platform)->where('product_id', $productId)->latest()->first();
        }

        if (! $purchase) {
            return;
        }

        $plan = SubscriptionPlan::where($platform === 'ios' ? 'apple_product_id' : 'google_product_id', $productId ?: $purchase->product_id)->first();
        if (! $plan) {
            return;
        }

        $purchase->update([
            'subscription_plan_id' => $plan->id,
            'product_id' => $productId ?: $purchase->product_id,
            'transaction_id' => $transactionId ?: $purchase->transaction_id,
            'status' => $status,
            'verified_at' => in_array($status, ['active', 'renewed', 'purchased'], true) ? now() : $purchase->verified_at,
            'raw_payload' => $payload,
        ]);

        if (in_array($status, ['active', 'renewed', 'purchased'], true)) {
            $this->subscriptions->activatePlan(
                $purchase->user,
                $plan,
                $platform === 'ios' ? 'app_store' : 'google_play',
                $transactionId ?: $purchase->transaction_id ?: $purchaseToken,
            );
            $this->notifications->subscriptionActivated($purchase->user, $plan);

            return;
        }

        if (in_array($status, ['canceled', 'expired', 'revoked', 'on_hold', 'pending_canceled'], true)) {
            $this->subscriptions->deactivateStorePlan($purchase->user, $purchase->transaction_id);
            $this->notifications->subscriptionChanged($purchase->user, $status);
        }
    }

    private function appleStatus(string $type, ?string $subtype): string
    {
        return match ($type) {
            'SUBSCRIBED', 'DID_RENEW', 'DID_CHANGE_RENEWAL_PREF', 'DID_RECOVER', 'ONE_TIME_CHARGE' => 'active',
            'EXPIRED' => 'expired',
            'REFUND', 'REVOKE', 'REFUND_DECLINED' => 'revoked',
            'DID_FAIL_TO_RENEW', 'GRACE_PERIOD_EXPIRED' => 'on_hold',
            'DID_CHANGE_RENEWAL_STATUS' => $subtype === 'AUTO_RENEW_DISABLED' ? 'canceled' : 'active',
            default => 'received',
        };
    }

    private function googleSubscriptionStatus(int $type): string
    {
        return match ($type) {
            1, 2, 4, 7 => $type === 2 ? 'renewed' : 'active',
            3 => 'canceled',
            5 => 'on_hold',
            12 => 'revoked',
            13 => 'expired',
            20 => 'pending_canceled',
            default => 'received',
        };
    }

    private function googleOneTimeStatus(int $type): string
    {
        return match ($type) {
            1 => 'purchased',
            2 => 'pending_canceled',
            default => 'received',
        };
    }

    private function decodeJwsPayload(string $jws): array
    {
        $parts = explode('.', $jws);
        if (count($parts) < 2) {
            return [];
        }

        $json = base64_decode(strtr($parts[1], '-_', '+/')) ?: '{}';

        return json_decode($json, true) ?: [];
    }
}
