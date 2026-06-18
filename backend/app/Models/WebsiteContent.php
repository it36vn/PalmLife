<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

#[Fillable(['key', 'group', 'label', 'type', 'value_vi', 'value_en'])]
class WebsiteContent extends Model
{
    public const DEFAULTS = [
        ['key' => 'site_name', 'group' => 'Common', 'label' => 'Tên ứng dụng', 'type' => 'text', 'value_vi' => 'Xem Chỉ Tay', 'value_en' => 'Palm Life'],
        ['key' => 'privacy_nav', 'group' => 'Common', 'label' => 'Link quyền riêng tư', 'type' => 'text', 'value_vi' => 'Quyền riêng tư', 'value_en' => 'Privacy'],
        ['key' => 'download_label', 'group' => 'Landing', 'label' => 'Nhãn tải app', 'type' => 'text', 'value_vi' => 'Tải ứng dụng', 'value_en' => 'Download app'],
        ['key' => 'app_store_prefix', 'group' => 'Landing', 'label' => 'Nhãn App Store', 'type' => 'text', 'value_vi' => 'Tải trên', 'value_en' => 'Download on the'],
        ['key' => 'google_play_prefix', 'group' => 'Landing', 'label' => 'Nhãn Google Play', 'type' => 'text', 'value_vi' => 'Tải trên', 'value_en' => 'Get it on'],
        ['key' => 'hero_description', 'group' => 'Landing', 'label' => 'Mô tả hero', 'type' => 'textarea', 'value_vi' => 'Ứng dụng hỗ trợ xem chỉ tay, tử vi và thần số học cho mục đích giải trí và tự phản chiếu. Chụp ảnh bàn tay và nhận gợi ý nhẹ nhàng, dễ hiểu chỉ trong vài chạm.', 'value_en' => 'Palm reading, astrology, and numerology for entertainment and self-reflection. Get gentle, easy-to-read suggestions from a palm photo in a few taps.'],
        ['key' => 'hero_primary_button', 'group' => 'Landing', 'label' => 'Nút hero chính', 'type' => 'text', 'value_vi' => 'Xem gói dịch vụ', 'value_en' => 'View subscriptions'],
        ['key' => 'hero_secondary_button', 'group' => 'Landing', 'label' => 'Nút hero phụ', 'type' => 'text', 'value_vi' => 'Cam kết an toàn', 'value_en' => 'Safety promise'],
        ['key' => 'preview_label', 'group' => 'Landing', 'label' => 'Nhãn preview app', 'type' => 'text', 'value_vi' => 'Xem trước ứng dụng', 'value_en' => 'App preview'],
        ['key' => 'mini_free_title', 'group' => 'Landing', 'label' => 'Mini card miễn phí - tiêu đề', 'type' => 'text', 'value_vi' => 'Dùng thử miễn phí', 'value_en' => 'Free trial'],
        ['key' => 'mini_free_body', 'group' => 'Landing', 'label' => 'Mini card miễn phí - nội dung', 'type' => 'text', 'value_vi' => 'Bắt đầu với lượt xem miễn phí.', 'value_en' => 'Start with free readings.'],
        ['key' => 'mini_privacy_title', 'group' => 'Landing', 'label' => 'Mini card riêng tư - tiêu đề', 'type' => 'text', 'value_vi' => 'Riêng tư', 'value_en' => 'Privacy'],
        ['key' => 'mini_privacy_body', 'group' => 'Landing', 'label' => 'Mini card riêng tư - nội dung', 'type' => 'text', 'value_vi' => 'Bạn kiểm soát dữ liệu tài khoản.', 'value_en' => 'You control your account data.'],
        ['key' => 'plans_title', 'group' => 'Landing', 'label' => 'Tiêu đề gói', 'type' => 'text', 'value_vi' => 'Gói dịch vụ', 'value_en' => 'Subscription plans'],
        ['key' => 'plan_footer', 'group' => 'Landing', 'label' => 'Mô tả trong mỗi gói', 'type' => 'text', 'value_vi' => 'Chọn gói trực tiếp trong ứng dụng khi bạn sẵn sàng.', 'value_en' => 'Choose in the app whenever you are ready.'],
        ['key' => 'safety_title', 'group' => 'Landing', 'label' => 'Tiêu đề cam kết', 'type' => 'text', 'value_vi' => 'Cam kết an toàn', 'value_en' => 'Safety promise'],
        ['key' => 'safety_1_title', 'group' => 'Landing', 'label' => 'Cam kết 1 - tiêu đề', 'type' => 'text', 'value_vi' => 'Chỉ giải trí', 'value_en' => 'Entertainment only'],
        ['key' => 'safety_1_body', 'group' => 'Landing', 'label' => 'Cam kết 1 - nội dung', 'type' => 'textarea', 'value_vi' => 'Nội dung dùng để giải trí và tự phản chiếu, không phải dự đoán tương lai chắc chắn.', 'value_en' => 'Readings are for fun and self-reflection, not guaranteed future predictions.'],
        ['key' => 'safety_2_title', 'group' => 'Landing', 'label' => 'Cam kết 2 - tiêu đề', 'type' => 'text', 'value_vi' => 'Tích cực, nhẹ nhàng', 'value_en' => 'Positive tone'],
        ['key' => 'safety_2_body', 'group' => 'Landing', 'label' => 'Cam kết 2 - nội dung', 'type' => 'textarea', 'value_vi' => 'Không gieo nỗi sợ, không ép mua, không nghi lễ hoặc hứa hẹn giải hạn.', 'value_en' => 'No fear-based claims, pressure, rituals, or promises to remove bad luck.'],
        ['key' => 'safety_3_title', 'group' => 'Landing', 'label' => 'Cam kết 3 - tiêu đề', 'type' => 'text', 'value_vi' => 'Quyền riêng tư', 'value_en' => 'Privacy controls'],
        ['key' => 'safety_3_body', 'group' => 'Landing', 'label' => 'Cam kết 3 - nội dung', 'type' => 'textarea', 'value_vi' => 'Quản lý tài khoản, đồng ý sử dụng và yêu cầu xoá dữ liệu ngay trong ứng dụng.', 'value_en' => 'Manage your account, consent, and deletion options from the app.'],
        ['key' => 'footer_text', 'group' => 'Landing', 'label' => 'Footer', 'type' => 'textarea', 'value_vi' => 'Xem Chỉ Tay giúp bạn khám phá bản thân bằng trải nghiệm nhẹ nhàng và tích cực hơn.', 'value_en' => 'Palm Life helps you explore yourself with a calmer, kinder reading experience.'],
        ['key' => 'privacy_title', 'group' => 'Privacy', 'label' => 'Tiêu đề privacy', 'type' => 'text', 'value_vi' => 'Chính sách quyền riêng tư', 'value_en' => 'Privacy Policy'],
        ['key' => 'privacy_updated', 'group' => 'Privacy', 'label' => 'Ngày cập nhật', 'type' => 'text', 'value_vi' => 'Cập nhật lần cuối: 08/06/2026', 'value_en' => 'Last updated: June 8, 2026'],
        ['key' => 'privacy_intro', 'group' => 'Privacy', 'label' => 'Mở đầu', 'type' => 'textarea', 'value_vi' => 'Xem Chỉ Tay giúp người dùng khám phá chỉ tay, tử vi và thần số học cho mục đích giải trí và tự phản chiếu. Chính sách này giải thích dữ liệu chúng tôi thu thập và cách người dùng kiểm soát dữ liệu của mình.', 'value_en' => 'Palm Life helps users explore palm reading, astrology, and numerology for entertainment and self-reflection. This policy explains what information we collect and how users can control it.'],
        ['key' => 'privacy_info_title', 'group' => 'Privacy', 'label' => 'Thông tin thu thập - tiêu đề', 'type' => 'text', 'value_vi' => 'Thông tin chúng tôi thu thập', 'value_en' => 'Information We Collect'],
        ['key' => 'privacy_info_items', 'group' => 'Privacy', 'label' => 'Thông tin thu thập - mỗi dòng là 1 ý', 'type' => 'textarea', 'value_vi' => "Thông tin tài khoản như tên, email và mật khẩu đã mã hoá.\nYêu cầu xem, kiểu xem, ngôn ngữ, kết quả và mã băm ảnh không thể hiện ảnh gốc.\nThông tin gói dịch vụ, giao dịch và subscription cần thiết để quản lý quyền sử dụng.\nGhi nhận đồng ý, yêu cầu hỗ trợ và log kỹ thuật cơ bản để bảo mật, vận hành ổn định.", 'value_en' => "Account information such as name, email address, and encrypted password.\nPalm reading requests, selected reading type, language, result, and a non-image hash used for reference.\nPurchase and subscription information required to manage plans.\nConsent records, support requests, and basic technical logs needed for security and reliability."],
        ['key' => 'privacy_use_title', 'group' => 'Privacy', 'label' => 'Cách dùng dữ liệu - tiêu đề', 'type' => 'text', 'value_vi' => 'Cách chúng tôi sử dụng thông tin', 'value_en' => 'How We Use Information'],
        ['key' => 'privacy_use_items', 'group' => 'Privacy', 'label' => 'Cách dùng dữ liệu - mỗi dòng là 1 ý', 'type' => 'textarea', 'value_vi' => "Tạo tài khoản, xác thực người dùng và cung cấp kết quả xem.\nQuản lý lượt xem, gói dịch vụ, giao dịch, thông báo và hỗ trợ khách hàng.\nBảo vệ dịch vụ, hạn chế lạm dụng và đáp ứng nghĩa vụ áp dụng.", 'value_en' => "To create accounts, authenticate users, and provide readings.\nTo manage quotas, subscriptions, purchases, notifications, and customer support.\nTo protect the service, prevent abuse, and comply with applicable obligations."],
        ['key' => 'privacy_images_title', 'group' => 'Privacy', 'label' => 'Ảnh bàn tay - tiêu đề', 'type' => 'text', 'value_vi' => 'Ảnh bàn tay', 'value_en' => 'Palm Images'],
        ['key' => 'privacy_images_body', 'group' => 'Privacy', 'label' => 'Ảnh bàn tay - nội dung', 'type' => 'textarea', 'value_vi' => 'Ảnh bàn tay được xử lý để tạo nội dung giải trí. Mặc định hệ thống lưu kết quả và mã băm ảnh, không lưu ảnh gốc đã tải lên.', 'value_en' => 'Palm images are processed to create entertainment-only readings. By default, the service keeps the reading result and image hash, not the original uploaded image.'],
        ['key' => 'privacy_controls_title', 'group' => 'Privacy', 'label' => 'Quyền kiểm soát - tiêu đề', 'type' => 'text', 'value_vi' => 'Quyền kiểm soát của người dùng', 'value_en' => 'User Controls'],
        ['key' => 'privacy_controls_body', 'group' => 'Privacy', 'label' => 'Quyền kiểm soát - nội dung', 'type' => 'textarea', 'value_vi' => 'Người dùng có thể cập nhật tài khoản, đổi mật khẩu, khôi phục mật khẩu và xoá tài khoản trong ứng dụng. Khi xoá tài khoản, hệ thống xoá lịch sử và dữ liệu liên quan của người dùng trong phạm vi pháp lý và kỹ thuật cho phép.', 'value_en' => 'Users can update their account, change password, request password recovery, and delete their account from the app. Account deletion removes user-owned reading history and related records where deletion is legally and technically permitted.'],
        ['key' => 'privacy_children_title', 'group' => 'Privacy', 'label' => 'Trẻ em - tiêu đề', 'type' => 'text', 'value_vi' => 'Trẻ em', 'value_en' => 'Children'],
        ['key' => 'privacy_children_body', 'group' => 'Privacy', 'label' => 'Trẻ em - nội dung', 'type' => 'textarea', 'value_vi' => 'Ứng dụng dành cho người dùng phổ thông và trẻ em chỉ nên sử dụng khi có hướng dẫn phù hợp từ phụ huynh hoặc người giám hộ.', 'value_en' => 'The app is intended for general audiences and should not be used by children without appropriate parental or guardian guidance.'],
        ['key' => 'privacy_contact_title', 'group' => 'Privacy', 'label' => 'Liên hệ - tiêu đề', 'type' => 'text', 'value_vi' => 'Liên hệ', 'value_en' => 'Contact'],
        ['key' => 'privacy_contact_body', 'group' => 'Privacy', 'label' => 'Liên hệ - nội dung', 'type' => 'textarea', 'value_vi' => 'Nếu có câu hỏi về quyền riêng tư, vui lòng gửi email :email hoặc gọi :phone.', 'value_en' => 'For privacy questions, contact us at :email or call :phone.'],
    ];

    public static function syncDefaults(): void
    {
        foreach (self::DEFAULTS as $item) {
            self::firstOrCreate(['key' => $item['key']], $item);
        }
    }

    public static function valuesFor(string $locale): Collection
    {
        self::syncDefaults();
        $column = $locale === 'en' ? 'value_en' : 'value_vi';

        return self::query()
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn (self $content): array => [$content->key => $content->{$column}]);
    }
}
