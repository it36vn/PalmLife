<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name_vi', 'name_en', 'price_vnd', 'quota_limit', 'quota_period', 'is_default', 'description_vi', 'description_en', 'apple_product_id', 'google_product_id', 'store_product_type'])]
class SubscriptionPlan extends Model
{
    use HasFactory;

    public const DEFAULTS = [
        ['code' => 'free', 'name_vi' => 'Miễn phí', 'name_en' => 'Free', 'price_vnd' => 0, 'quota_limit' => 5, 'quota_period' => 'lifetime', 'is_default' => true, 'description_vi' => 'Xem thử 5 lần, mặc định khi tạo tài khoản.', 'description_en' => 'Try 5 readings, assigned by default.', 'apple_product_id' => 'com.it36vn.xemchitay1', 'google_product_id' => 'com.it36vn.xemchitay1', 'store_product_type' => 'free'],
        ['code' => 'standard', 'name_vi' => 'Thường', 'name_en' => 'Standard', 'price_vnd' => 29000, 'quota_limit' => 5, 'quota_period' => 'month', 'is_default' => false, 'description_vi' => 'Mỗi tháng xem 5 lần.', 'description_en' => '5 readings every month.', 'apple_product_id' => 'com.it36vn.xemchitay2', 'google_product_id' => 'com.it36vn.xemchitay2', 'store_product_type' => 'subscription'],
        ['code' => 'advanced', 'name_vi' => 'Nâng cao', 'name_en' => 'Advanced', 'price_vnd' => 59000, 'quota_limit' => 5, 'quota_period' => 'week', 'is_default' => false, 'description_vi' => 'Mỗi tuần xem 5 lần.', 'description_en' => '5 readings every week.', 'apple_product_id' => 'com.it36vn.xemchitay3', 'google_product_id' => 'com.it36vn.xemchitay3', 'store_product_type' => 'subscription'],
        ['code' => 'vip', 'name_vi' => 'VIP', 'name_en' => 'VIP', 'price_vnd' => 990000, 'quota_limit' => 15, 'quota_period' => 'week', 'is_default' => false, 'description_vi' => 'Mỗi tuần xem 15 lần.', 'description_en' => '15 readings every week.', 'apple_product_id' => 'com.it36vn.xemchitay4', 'google_product_id' => 'com.it36vn.xemchitay4', 'store_product_type' => 'subscription'],
        ['code' => 'lifetime', 'name_vi' => 'Vĩnh viễn', 'name_en' => 'Lifetime', 'price_vnd' => 199000, 'quota_limit' => null, 'quota_period' => 'unlimited', 'is_default' => false, 'description_vi' => 'Không giới hạn số lần xem.', 'description_en' => 'Unlimited readings.', 'apple_product_id' => 'com.it36vn.xemchitay5', 'google_product_id' => 'com.it36vn.xemchitay5', 'store_product_type' => 'non_consumable'],
    ];

    public static function syncDefaults(): void
    {
        foreach (self::DEFAULTS as $plan) {
            self::firstOrCreate(['code' => $plan['code']], $plan);
        }
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }
}
