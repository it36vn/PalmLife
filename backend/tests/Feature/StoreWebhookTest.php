<?php

namespace Tests\Feature;

use App\Models\StorePurchase;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_success_notification_updates_user_subscription(): void
    {
        SubscriptionPlan::syncDefaults();
        $user = User::factory()->create();
        $plan = SubscriptionPlan::where('code', 'standard')->firstOrFail();

        StorePurchase::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'platform' => 'android',
            'product_id' => $plan->google_product_id,
            'purchase_token' => 'purchase-token-123',
            'transaction_id' => 'GPA.123',
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/store/webhooks/google', [
            'version' => '1.0',
            'packageName' => 'com.it36vn.xemchitay',
            'eventTimeMillis' => '1780812345000',
            'subscriptionNotification' => [
                'version' => '1.0',
                'notificationType' => 4,
                'purchaseToken' => 'purchase-token-123',
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'is_active' => true,
            'payment_provider' => 'google_play',
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'type' => 'subscription_activated',
        ]);
    }
}
