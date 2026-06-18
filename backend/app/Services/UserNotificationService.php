<?php

namespace App\Services;

use App\Models\SubscriptionPlan;
use App\Models\User;

class UserNotificationService
{
    public function subscriptionActivated(User $user, SubscriptionPlan $plan): void
    {
        $user->notifications()->create([
            'type' => 'subscription_activated',
            'title_vi' => 'Gói dịch vụ đã được cập nhật',
            'title_en' => 'Subscription updated',
            'body_vi' => 'Gói '.$plan->name_vi.' đã được kích hoạt thành công.',
            'body_en' => 'Your '.$plan->name_en.' plan has been activated.',
            'data' => ['plan_code' => $plan->code],
        ]);
    }

    public function subscriptionChanged(User $user, string $status): void
    {
        $user->notifications()->create([
            'type' => 'subscription_status_changed',
            'title_vi' => 'Trạng thái gói đã thay đổi',
            'title_en' => 'Subscription status changed',
            'body_vi' => 'Store đã cập nhật trạng thái gói: '.$status.'. Ứng dụng đã đồng bộ lại quyền sử dụng.',
            'body_en' => 'The store reported subscription status: '.$status.'. Your app entitlement was refreshed.',
            'data' => ['status' => $status],
        ]);
    }
}
