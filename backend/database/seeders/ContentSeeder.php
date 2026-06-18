<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'hero_title', 'locale' => 'vi', 'content' => 'Xem Chỉ Tay'],
            ['key' => 'hero_title', 'locale' => 'en', 'content' => 'Palm Life'],
            ['key' => 'hero_description', 'locale' => 'vi', 'content' => 'Ứng dụng hỗ trợ xem chỉ tay, tử vi và thần số học cho mục đích giải trí và tự phản chiếu. Chụp ảnh bàn tay và nhận gợi ý nhẹ nhàng, dễ hiểu chỉ trong vài chạm.'],
            ['key' => 'hero_description', 'locale' => 'en', 'content' => 'Palm reading, astrology, and numerology for entertainment and self-reflection. Get gentle, easy-to-read suggestions from a palm photo in a few taps.'],
            ['key' => 'plans_heading', 'locale' => 'vi', 'content' => 'Gói dịch vụ'],
            ['key' => 'plans_heading', 'locale' => 'en', 'content' => 'Subscription plans'],
            ['key' => 'safety_heading', 'locale' => 'vi', 'content' => 'Cam kết an toàn'],
            ['key' => 'safety_heading', 'locale' => 'en', 'content' => 'Safety promise'],
        ];

        foreach ($defaults as $row) {
            DB::table('contents')->updateOrInsert(
                ['key' => $row['key'], 'locale' => $row['locale']],
                ['content' => $row['content']]
            );
        }
    }
}
