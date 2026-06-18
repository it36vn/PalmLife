<?php

namespace App\Services;

use App\Models\SubscriptionPlan;

class StorePurchaseVerifier
{
    public function verify(string $platform, SubscriptionPlan $plan, string $purchaseToken, ?string $transactionId): array
    {
        if (config('services.store.verification_mode') === 'local_accept' && app()->isLocal()) {
            return [
                'valid' => true,
                'status' => 'verified_local',
                'transaction_id' => $transactionId ?: 'local-'.hash('sha256', $platform.$plan->code.$purchaseToken),
                'raw' => ['mode' => 'local_accept'],
            ];
        }

        return match ($platform) {
            'ios' => $this->verifyApple($plan, $purchaseToken, $transactionId),
            'android' => $this->verifyGoogle($plan, $purchaseToken, $transactionId),
            default => ['valid' => false, 'status' => 'unsupported_platform', 'raw' => null],
        };
    }

    private function verifyApple(SubscriptionPlan $plan, string $signedTransaction, ?string $transactionId): array
    {
        if (! config('services.store.apple_issuer_id') || ! config('services.store.apple_key_id')) {
            return [
                'valid' => false,
                'status' => 'apple_server_api_not_configured',
                'transaction_id' => $transactionId,
                'raw' => null,
            ];
        }

        return [
            'valid' => false,
            'status' => 'apple_server_api_adapter_pending',
            'transaction_id' => $transactionId,
            'raw' => ['expected_product_id' => $plan->apple_product_id],
        ];
    }

    private function verifyGoogle(SubscriptionPlan $plan, string $purchaseToken, ?string $transactionId): array
    {
        if (! config('services.store.google_package_name') || ! config('services.store.google_service_account_json')) {
            return [
                'valid' => false,
                'status' => 'google_play_developer_api_not_configured',
                'transaction_id' => $transactionId,
                'raw' => null,
            ];
        }

        return [
            'valid' => false,
            'status' => 'google_play_api_adapter_pending',
            'transaction_id' => $transactionId,
            'raw' => [
                'expected_product_id' => $plan->google_product_id,
                'api' => 'purchases.subscriptionsv2.get',
            ],
        ];
    }
}
