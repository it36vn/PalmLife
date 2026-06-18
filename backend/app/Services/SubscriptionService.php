<?php

namespace App\Services;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Carbon;

class SubscriptionService
{
    public function ensureDefaultSubscription(User $user): UserSubscription
    {
        $active = $this->activeSubscription($user);
        if ($active !== null) {
            return $active;
        }

        SubscriptionPlan::syncDefaults();
        $plan = SubscriptionPlan::where('is_default', true)->firstOrFail();

        return $this->activatePlan($user, $plan, 'system', 'default-free');
    }

    public function activeSubscription(User $user): ?UserSubscription
    {
        return $user->subscriptions()
            ->with('plan')
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->latest()
            ->first();
    }

    public function activatePlan(User $user, SubscriptionPlan $plan, ?string $provider = null, ?string $reference = null): UserSubscription
    {
        $user->subscriptions()->where('is_active', true)->update(['is_active' => false]);

        return $user->subscriptions()->create([
            'subscription_plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => $this->endDateFor($plan),
            'is_active' => true,
            'payment_provider' => $provider,
            'payment_reference' => $reference,
        ])->load('plan');
    }

    public function deactivateStorePlan(User $user, ?string $reference = null): UserSubscription
    {
        $query = $user->subscriptions()->where('is_active', true);
        if ($reference !== null) {
            $query->where('payment_reference', $reference);
        }
        $query->update(['is_active' => false, 'ends_at' => now()]);

        SubscriptionPlan::syncDefaults();
        $freePlan = SubscriptionPlan::where('is_default', true)->firstOrFail();

        return $this->activatePlan($user, $freePlan, 'system', 'store-fallback-free');
    }

    public function quotaStatus(User $user): array
    {
        $subscription = $this->ensureDefaultSubscription($user)->load('plan');
        $plan = $subscription->plan;

        if ($plan->quota_period === 'unlimited') {
            return [
                'allowed' => true,
                'used' => 0,
                'remaining' => null,
                'limit' => null,
                'period' => 'unlimited',
                'resets_at' => null,
                'subscription' => $subscription,
            ];
        }

        [$start, $end] = $this->usageWindow($subscription);
        $used = $user->analysisRequests()->whereBetween('created_at', [$start, $end])->count();
        $limit = $plan->quota_limit ?? 0;

        return [
            'allowed' => $used < $limit,
            'used' => $used,
            'remaining' => max(0, $limit - $used),
            'limit' => $limit,
            'period' => $plan->quota_period,
            'resets_at' => $plan->quota_period === 'lifetime' ? null : $end->toIso8601String(),
            'subscription' => $subscription,
        ];
    }

    private function usageWindow(UserSubscription $subscription): array
    {
        $plan = $subscription->plan;
        $now = now();
        $start = Carbon::parse($subscription->starts_at);

        return match ($plan->quota_period) {
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            default => [$start, $now->copy()->addYears(100)],
        };
    }

    private function endDateFor(SubscriptionPlan $plan): ?Carbon
    {
        return match ($plan->quota_period) {
            'week' => now()->addWeek(),
            'month' => now()->addMonth(),
            default => null,
        };
    }
}
