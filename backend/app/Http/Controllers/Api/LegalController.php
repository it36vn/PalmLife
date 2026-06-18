<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LegalController extends Controller
{
    public function show(Request $request)
    {
        $locale = $request->query('locale', 'vi') === 'en' ? 'en' : 'vi';

        return response()->json([
            'version' => '2026-06-07',
            'locale' => $locale,
            'ai_disclaimer' => $locale === 'en'
                ? 'This app is for entertainment and self-reflection only. It does not provide medical, legal, financial, spiritual, or guaranteed future predictions.'
                : 'Ứng dụng chỉ phục vụ giải trí và tự phản chiếu. Nội dung không phải lời khuyên y tế, pháp lý, tài chính, tâm linh hoặc dự đoán tương lai chắc chắn.',
            'privacy_notice' => $locale === 'en'
                ? 'Palm images are processed to create readings. The server stores only a hash and result by default, not the uploaded image.'
                : 'Ảnh bàn tay được xử lý để tạo nội dung xem. Máy chủ mặc định chỉ lưu mã băm và kết quả, không lưu ảnh tải lên.',
            'content_policy' => $locale === 'en'
                ? 'The app avoids fear-based claims, rituals, curses, cures, luck-removal services, and high-stakes recommendations.'
                : 'Ứng dụng tránh tuyên bố gây sợ hãi, nghi lễ, bùa chú, chữa bệnh, giải hạn hoặc khuyến nghị cho quyết định quan trọng.',
        ]);
    }
}
