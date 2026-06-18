<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request, SubscriptionService $subscriptions)
    {
        SubscriptionPlan::syncDefaults();

        return response()->json([
            'plans' => SubscriptionPlan::orderBy('price_vnd')->get(),
            'quota' => $request->user() ? $subscriptions->quotaStatus($request->user()) : null,
        ]);
    }

    public function checkout(Request $request, SubscriptionService $subscriptions)
    {
        abort_unless(app()->isLocal() && config('services.store.verification_mode') === 'local_accept', 403, 'Use App Store or Google Play billing for subscriptions.');

        $data = $request->validate([
            'plan_code' => ['required', 'exists:subscription_plans,code'],
            'payment_provider' => ['nullable', 'in:mock,momo,zalopay,vnpay,app_store,google_play'],
        ]);

        $plan = SubscriptionPlan::where('code', $data['plan_code'])->firstOrFail();

        if (($data['payment_provider'] ?? 'mock') === 'mock') {
            $subscription = $subscriptions->activatePlan($request->user(), $plan, 'mock', 'demo-'.now()->timestamp);

            return response()->json([
                'status' => 'activated',
                'subscription' => $subscription,
                'quota' => $subscriptions->quotaStatus($request->user()),
            ]);
        }

        return response()->json([
            'status' => 'pending',
            'checkout_url' => null,
            'message' => 'Payment provider adapter is ready for merchant credentials.',
        ], 202);
    }
}
