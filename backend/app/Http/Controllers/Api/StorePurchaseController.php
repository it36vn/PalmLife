<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StorePurchase;
use App\Models\SubscriptionPlan;
use App\Services\StorePurchaseVerifier;
use App\Services\SubscriptionService;
use App\Services\UserNotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StorePurchaseController extends Controller
{
    public function verify(
        Request $request,
        StorePurchaseVerifier $verifier,
        SubscriptionService $subscriptions,
        UserNotificationService $notifications,
    )
    {
        $data = $request->validate([
            'platform' => ['required', 'in:ios,android'],
            'product_id' => ['required', 'string', 'max:120'],
            'purchase_token' => ['required', 'string'],
            'transaction_id' => ['nullable', 'string', 'max:190'],
        ]);

        $productColumn = $data['platform'] === 'ios' ? 'apple_product_id' : 'google_product_id';
        $plan = SubscriptionPlan::where($productColumn, $data['product_id'])->first();

        if (! $plan || $plan->code === 'free') {
            throw ValidationException::withMessages(['product_id' => 'Unknown store product id.']);
        }

        $verification = $verifier->verify(
            $data['platform'],
            $plan,
            $data['purchase_token'],
            $data['transaction_id'] ?? null,
        );

        if (! $verification['valid']) {
            StorePurchase::create([
                'user_id' => $request->user()->id,
                'subscription_plan_id' => $plan->id,
                'platform' => $data['platform'],
                'product_id' => $data['product_id'],
                'purchase_token' => $data['purchase_token'],
                'transaction_id' => $verification['transaction_id'] ?? $data['transaction_id'] ?? null,
                'status' => $verification['status'],
                'raw_payload' => $verification['raw'],
            ]);

            return response()->json([
                'status' => $verification['status'],
                'message' => 'Store verification is not complete. Configure server-side store credentials before production.',
            ], 422);
        }

        StorePurchase::updateOrCreate(
            [
                'platform' => $data['platform'],
                'transaction_id' => $verification['transaction_id'],
            ],
            [
                'user_id' => $request->user()->id,
                'subscription_plan_id' => $plan->id,
                'product_id' => $data['product_id'],
                'purchase_token' => $data['purchase_token'],
                'status' => $verification['status'],
                'verified_at' => now(),
                'raw_payload' => $verification['raw'],
            ],
        );

        $subscription = $subscriptions->activatePlan(
            $request->user(),
            $plan,
            $data['platform'] === 'ios' ? 'app_store' : 'google_play',
            $verification['transaction_id'],
        );
        $notifications->subscriptionActivated($request->user(), $plan);

        return response()->json([
            'status' => 'activated',
            'subscription' => $subscription,
            'quota' => $subscriptions->quotaStatus($request->user()),
        ]);
    }
}
