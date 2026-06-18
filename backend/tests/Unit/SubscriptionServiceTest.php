<?php

namespace Tests\Unit;

use App\Models\AnalysisRequest;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_weekly_quota_resets_on_a_new_week(): void
    {
        Carbon::setTestNow('2026-06-15 10:00:00');

        $user = User::factory()->create();
        $plan = $this->plan('advanced', 2, 'week');
        app(SubscriptionService::class)->activatePlan($user, $plan);

        $this->analysisAt($user, '2026-06-14 10:00:00');
        $this->analysisAt($user, '2026-06-15 09:00:00');

        $quota = app(SubscriptionService::class)->quotaStatus($user);

        $this->assertTrue($quota['allowed']);
        $this->assertSame(1, $quota['used']);
        $this->assertSame(1, $quota['remaining']);
        $this->assertSame('week', $quota['period']);
    }

    public function test_monthly_quota_resets_on_a_new_month(): void
    {
        Carbon::setTestNow('2026-07-01 10:00:00');

        $user = User::factory()->create();
        $plan = $this->plan('standard', 2, 'month');
        app(SubscriptionService::class)->activatePlan($user, $plan);

        $this->analysisAt($user, '2026-06-30 10:00:00');
        $this->analysisAt($user, '2026-07-01 09:00:00');

        $quota = app(SubscriptionService::class)->quotaStatus($user);

        $this->assertTrue($quota['allowed']);
        $this->assertSame(1, $quota['used']);
        $this->assertSame(1, $quota['remaining']);
        $this->assertSame('month', $quota['period']);
    }

    public function test_lifetime_trial_is_limited_but_unlimited_plan_is_not(): void
    {
        Carbon::setTestNow('2026-06-15 10:00:00');

        $user = User::factory()->create();
        $trial = $this->plan('free', 1, 'lifetime');
        app(SubscriptionService::class)->activatePlan($user, $trial);
        $this->analysisAt($user, '2026-06-15 09:00:00');

        $quota = app(SubscriptionService::class)->quotaStatus($user);

        $this->assertFalse($quota['allowed']);
        $this->assertSame(0, $quota['remaining']);
        $this->assertSame('lifetime', $quota['period']);
        $this->assertNull($quota['resets_at']);

        $unlimited = $this->plan('lifetime', null, 'unlimited');
        app(SubscriptionService::class)->activatePlan($user, $unlimited);

        $quota = app(SubscriptionService::class)->quotaStatus($user);

        $this->assertTrue($quota['allowed']);
        $this->assertNull($quota['remaining']);
        $this->assertSame('unlimited', $quota['period']);
    }

    private function plan(string $code, ?int $limit, string $period): SubscriptionPlan
    {
        return SubscriptionPlan::query()->create([
            'code' => $code,
            'name_vi' => $code,
            'name_en' => $code,
            'price_vnd' => 0,
            'quota_limit' => $limit,
            'quota_period' => $period,
            'is_default' => false,
            'description_vi' => $code,
            'description_en' => $code,
            'apple_product_id' => "ios.$code",
            'google_product_id' => "android.$code",
            'store_product_type' => $period === 'unlimited'
                ? 'non_consumable'
                : 'subscription',
        ]);
    }

    private function analysisAt(User $user, string $createdAt): void
    {
        $analysis = AnalysisRequest::query()->create([
            'user_id' => $user->id,
            'type' => 'palm',
            'locale' => 'vi',
            'input_hash' => hash('sha256', $createdAt),
            'result' => ['title' => 'Bản đọc chỉ tay'],
            'disclaimer_acknowledged_at' => $createdAt,
        ]);
        $analysis->forceFill([
            'created_at' => Carbon::parse($createdAt),
            'updated_at' => Carbon::parse($createdAt),
        ])->save();
    }
}
