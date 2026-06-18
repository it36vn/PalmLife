<?php

namespace App\Http\Controllers;

use App\Models\AnalysisRequest;
use App\Models\StorePurchase;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserSubscription;
use App\Models\WebsiteContent;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard(Request $request): View
    {
        $locale = $this->adminLocale($request);
        $tr = $this->translations($locale);
        SubscriptionPlan::syncDefaults();

        $days = collect(range(6, 0))->map(fn (int $offset) => CarbonImmutable::today()->subDays($offset));
        $rawReadings = AnalysisRequest::query()
            ->where('created_at', '>=', CarbonImmutable::today()->subDays(6))
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $chart = $days->map(fn (CarbonImmutable $day) => [
            'label' => $day->format('d/m'),
            'value' => (int) ($rawReadings[$day->toDateString()] ?? 0),
        ])->values();

        return view('admin', [
            'page' => 'dashboard',
            'adminLocale' => $locale,
            'tr' => $tr,
            'title' => $tr['dashboard'],
            'stats' => [
                ['label' => $tr['users'], 'value' => User::count(), 'tone' => 'green'],
                ['label' => $tr['ai_readings'], 'value' => AnalysisRequest::count(), 'tone' => 'gold'],
                ['label' => $tr['active_plans'], 'value' => UserSubscription::where('is_active', true)->count(), 'tone' => 'blue'],
                ['label' => $tr['store_purchases'], 'value' => StorePurchase::count(), 'tone' => 'rose'],
            ],
            'chart' => $chart,
            'latestUsers' => User::latest()->limit(6)->get(),
            'latestReadings' => AnalysisRequest::with('user')->latest()->limit(8)->get(),
            'plans' => SubscriptionPlan::orderBy('price_vnd')->get(),
        ]);
    }

    public function users(Request $request): View
    {
        $locale = $this->adminLocale($request);
        $tr = $this->translations($locale);
        $search = trim((string) $request->query('q', ''));
        $users = User::query()
            ->withCount('analysisRequests')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin', [
            'page' => 'users',
            'adminLocale' => $locale,
            'tr' => $tr,
            'title' => $tr['users'],
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function analyses(Request $request): View
    {
        $locale = $this->adminLocale($request);
        $tr = $this->translations($locale);
        $type = (string) $request->query('type', '');
        $analyses = AnalysisRequest::query()
            ->with('user')
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin', [
            'page' => 'analyses',
            'adminLocale' => $locale,
            'tr' => $tr,
            'title' => $tr['ai_history'],
            'analyses' => $analyses,
            'type' => $type,
        ]);
    }

    public function notifications(Request $request): View
    {
        $locale = $this->adminLocale($request);
        $tr = $this->translations($locale);

        return view('admin', [
            'page' => 'notifications',
            'adminLocale' => $locale,
            'tr' => $tr,
            'title' => $tr['notifications'],
            'notificationUsers' => User::orderBy('name')->orderBy('email')->get(['id', 'name', 'email']),
            'notifications' => UserNotification::query()
                ->with('user')
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function storeNotification(Request $request): RedirectResponse
    {
        $tr = $this->translations($this->adminLocale($request));
        $data = $request->validate([
            'target' => ['required', 'in:all,selected'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'title_vi' => ['required', 'string', 'max:255'],
            'title_en' => ['required', 'string', 'max:255'],
            'body_vi' => ['required', 'string', 'max:2000'],
            'body_en' => ['required', 'string', 'max:2000'],
        ]);

        if ($data['target'] === 'selected' && empty($data['user_ids'])) {
            return back()
                ->withErrors(['user_ids' => $tr['notification_selected_required']])
                ->withInput();
        }

        $query = User::query();
        if ($data['target'] === 'selected') {
            $query->whereIn('id', $data['user_ids']);
        }

        $count = 0;
        $query->orderBy('id')->chunkById(200, function ($users) use ($data, &$count): void {
            $now = now();
            $rows = $users->map(fn (User $user): array => [
                'user_id' => $user->id,
                'type' => 'admin',
                'title_vi' => $data['title_vi'],
                'title_en' => $data['title_en'],
                'body_vi' => $data['body_vi'],
                'body_en' => $data['body_en'],
                'data' => json_encode(['source' => 'admin']),
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            if ($rows !== []) {
                UserNotification::insert($rows);
                $count += count($rows);
            }
        });

        return redirect('/admin/notifications')->with('status', "{$tr['notification_sent']} {$count}.");
    }

    public function plans(Request $request): View
    {
        $locale = $this->adminLocale($request);
        $tr = $this->translations($locale);
        SubscriptionPlan::syncDefaults();

        return view('admin', [
            'page' => 'plans',
            'adminLocale' => $locale,
            'tr' => $tr,
            'title' => $tr['plans'],
            'plans' => SubscriptionPlan::orderBy('price_vnd')->paginate(10),
        ]);
    }

    public function storePlan(Request $request): RedirectResponse
    {
        SubscriptionPlan::create($this->planData($request));
        $tr = $this->translations($this->adminLocale($request));

        return redirect('/admin/plans')->with('status', $tr['plan_created']);
    }

    public function updatePlan(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $plan->update($this->planData($request, $plan));
        $tr = $this->translations($this->adminLocale($request));

        return redirect('/admin/plans')->with('status', $tr['plan_updated']);
    }

    public function destroyPlan(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $tr = $this->translations($this->adminLocale($request));
        if ($plan->subscriptions()->exists()) {
            return redirect('/admin/plans')->with('status', $tr['plan_delete_blocked']);
        }

        $plan->delete();

        return redirect('/admin/plans')->with('status', $tr['plan_deleted']);
    }

    public function contents(Request $request): View
    {
        $locale = $this->adminLocale($request);
        $tr = $this->translations($locale);
        WebsiteContent::syncDefaults();
        $contents = WebsiteContent::orderBy('group')->orderBy('id')->get();
        $groups = $contents->groupBy('group');

        return view('admin', [
            'page' => 'contents',
            'adminLocale' => $locale,
            'tr' => $tr,
            'title' => $tr['site_content'],
            'contentGroups' => $groups,
        ]);
    }

    public function updateContents(Request $request): RedirectResponse
    {
        $tr = $this->translations($this->adminLocale($request));
        $data = $request->input('content', []);

        foreach ($data as $id => $values) {
            $item = WebsiteContent::find($id);
            if (!$item) {
                continue;
            }
            if (isset($values['value_vi'])) {
                $item->value_vi = $values['value_vi'];
            }
            if (isset($values['value_en'])) {
                $item->value_en = $values['value_en'];
            }
            $item->save();
        }

        return redirect('/admin/contents')->with('status', $tr['content_saved']);
    }

    public function toggleUserLock(Request $request, User $user): RedirectResponse
    {
        $tr = $this->translations($this->adminLocale($request));
        $locked = $user->locked_at !== null;
        $user->forceFill([
            'locked_at' => $locked ? null : now(),
        ])->save();

        $message = $locked
            ? "{$tr['user_unlocked']} {$user->email}."
            : "{$tr['user_locked']} {$user->email}.";

        return redirect('/admin/users')->with('status', $message);
    }

    private function planData(Request $request, ?SubscriptionPlan $plan = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:60', Rule::unique('subscription_plans', 'code')->ignore($plan)],
            'name_vi' => ['required', 'string', 'max:120'],
            'name_en' => ['required', 'string', 'max:120'],
            'price_vnd' => ['required', 'integer', 'min:0'],
            'quota_limit' => ['nullable', 'integer', 'min:0'],
            'quota_period' => ['required', 'string', 'max:40'],
            'description_vi' => ['required', 'string', 'max:500'],
            'description_en' => ['required', 'string', 'max:500'],
            'apple_product_id' => ['required', 'string', 'max:160', Rule::unique('subscription_plans', 'apple_product_id')->ignore($plan)],
            'google_product_id' => ['required', 'string', 'max:160', Rule::unique('subscription_plans', 'google_product_id')->ignore($plan)],
            'store_product_type' => ['required', 'string', 'max:60'],
            'is_default' => ['nullable', 'boolean'],
        ]) + ['is_default' => false];
    }

    private function adminLocale(Request $request): string
    {
        if ($request->query('admin_locale') === 'en' || $request->query('admin_locale') === 'vi') {
            $request->session()->put('admin_locale', $request->query('admin_locale'));
        }

        return $request->session()->get('admin_locale') === 'en' ? 'en' : 'vi';
    }

    private function translations(string $locale): array
    {
        $en = $locale === 'en';

        return [
            'app_name' => $en ? 'Palm Life' : 'Xem Chỉ Tay',
            'dashboard' => $en ? 'Dashboard' : 'Tổng quan',
            'users' => $en ? 'Users' : 'Người dùng',
            'ai_history' => $en ? 'Reading history' : 'Lịch sử xem',
            'notifications' => $en ? 'Notifications' : 'Thông báo',
            'plans' => $en ? 'Plans' : 'Gói dịch vụ',
            'site_content' => $en ? 'Website content' : 'Nội dung website',
            'admin_console' => $en ? 'Admin console' : 'Quản trị',
            'subtitle' => $en ? 'Manage data, monitor readings, and configure plans.' : 'Quản trị dữ liệu, theo dõi lượt xem và cấu hình gói bán hàng.',
            'open_landing' => $en ? 'Open landing' : 'Mở landing',
            'logout' => $en ? 'Log out' : 'Đăng xuất',
            'ai_readings' => $en ? 'Readings' : 'Lượt xem',
            'active_plans' => $en ? 'Active plans' : 'Gói đang active',
            'store_purchases' => $en ? 'Store purchases' : 'Giao dịch store',
            'readings_7_days' => $en ? 'Readings in 7 days' : 'Lượt xem 7 ngày',
            'chart_label' => $en ? 'Readings chart' : 'Biểu đồ lượt xem',
            'selling_plans' => $en ? 'Published plans' : 'Gói đang bán',
            'unlimited' => $en ? 'Unlimited' : 'Không giới hạn',
            'readings' => $en ? 'readings' : 'lượt',
            'latest_users' => $en ? 'New users' : 'Người dùng mới',
            'latest_readings' => $en ? 'New readings' : 'Lượt xem mới',
            'deleted_user' => $en ? 'Deleted user' : 'Người dùng đã xoá',
            'user_list' => $en ? 'User list' : 'Danh sách người dùng',
            'search_placeholder' => $en ? 'Search name or email' : 'Tìm tên hoặc email',
            'search' => $en ? 'Search' : 'Tìm',
            'id' => 'ID',
            'name' => $en ? 'Name' : 'Tên',
            'email' => 'Email',
            'status' => $en ? 'Status' : 'Trạng thái',
            'title' => $en ? 'Title' : 'Tiêu đề',
            'content' => $en ? 'Content' : 'Nội dung',
            'read_count' => $en ? 'Readings' : 'Lượt xem',
            'created_at' => $en ? 'Created at' : 'Ngày tạo',
            'actions' => $en ? 'Actions' : 'Thao tác',
            'locked' => $en ? 'Locked' : 'Đã khoá',
            'active' => $en ? 'Active' : 'Đang mở',
            'unlock' => $en ? 'Unlock' : 'Mở khoá',
            'lock' => $en ? 'Lock' : 'Khoá',
            'all_types' => $en ? 'All reading types' : 'Tất cả kiểu xem',
            'filter' => $en ? 'Filter' : 'Lọc',
            'type' => $en ? 'Type' : 'Kiểu',
            'provider' => $en ? 'Source' : 'Nguồn',
            'image_hash' => $en ? 'Image hash' : 'Hash ảnh',
            'send_notification' => $en ? 'Send notification' : 'Gửi thông báo',
            'notification_target_hint' => $en ? 'Choose all users or selected users.' : 'Chọn gửi tất cả hoặc một nhóm người dùng.',
            'send_to' => $en ? 'Send to' : 'Gửi tới',
            'send_all_users' => $en ? 'All users' : 'Tất cả người dùng',
            'send_selected_users' => $en ? 'Selected users' : 'Người dùng được chọn',
            'selected_users' => $en ? 'Selected users' : 'Người dùng được chọn',
            'selected_users_hint' => $en ? 'Hold Cmd/Ctrl to select multiple users.' : 'Giữ Cmd/Ctrl để chọn nhiều người dùng.',
            'title_vi' => $en ? 'Vietnamese title' : 'Tiêu đề tiếng Việt',
            'title_en' => $en ? 'English title' : 'Tiêu đề tiếng Anh',
            'body_vi' => $en ? 'Vietnamese message' : 'Nội dung tiếng Việt',
            'body_en' => $en ? 'English message' : 'Nội dung tiếng Anh',
            'send_notification_button' => $en ? 'Send notification' : 'Gửi thông báo',
            'recent_notifications' => $en ? 'Recent notifications' : 'Thông báo đã gửi',
            'notification_sent' => $en ? 'Sent notifications:' : 'Đã gửi thông báo:',
            'notification_selected_required' => $en ? 'Select at least one user.' : 'Vui lòng chọn ít nhất một người dùng.',
            'read' => $en ? 'Read' : 'Đã đọc',
            'unread' => $en ? 'Unread' : 'Chưa đọc',
            'reading_types' => [
                'combined' => $en ? 'Combined' : 'Tổng hợp',
                'palm' => $en ? 'Palm' : 'Chỉ tay',
                'astrology' => $en ? 'Astrology' : 'Tử vi',
                'numerology' => $en ? 'Numerology' : 'Thần số học',
            ],
            'add_plan' => $en ? 'Add plan' : 'Thêm gói mới',
            'add_plan_button' => $en ? 'Add plan' : 'Thêm gói',
            'manage_plans' => $en ? 'Manage plans' : 'Quản lý gói',
            'plan_count' => $en ? 'plans' : 'gói',
            'save_changes' => $en ? 'Save changes' : 'Lưu thay đổi',
            'delete_plan' => $en ? 'Delete plan' : 'Xoá gói',
            'collapse_hint' => $en ? 'Click to expand or collapse this section.' : 'Click để mở hoặc thu gọn phần này.',
            'plan_created' => $en ? 'Plan has been added.' : 'Đã thêm gói dịch vụ.',
            'plan_updated' => $en ? 'Plan has been updated.' : 'Đã cập nhật gói dịch vụ.',
            'plan_deleted' => $en ? 'Plan has been deleted.' : 'Đã xoá gói dịch vụ.',
            'plan_delete_blocked' => $en ? 'Cannot delete a plan that already has users.' : 'Không thể xoá gói đã có người dùng.',
            'user_locked' => $en ? 'Locked user' : 'Đã khoá người dùng',
            'user_unlocked' => $en ? 'Unlocked user' : 'Đã mở khoá người dùng',
            'language' => $en ? 'Language' : 'Ngôn ngữ',
            'vietnamese' => 'Tiếng Việt',
            'english' => 'English',
            'back_to_landing' => $en ? 'Back to landing' : 'Quay lại landing',
            'password' => $en ? 'Admin password' : 'Mật khẩu admin',
            'login_title' => $en ? 'Admin console' : 'Quản trị',
            'login_intro' => $en ? 'Sign in to manage data, plans, and reading history.' : 'Đăng nhập để quản trị dữ liệu, gói dịch vụ và lịch sử xem.',
            'login_button' => $en ? 'Sign in' : 'Đăng nhập',
            'currency_suffix' => $en ? 'VND' : 'đ',
            'pagination' => $en ? 'Pagination' : 'Phân trang',
            'previous' => $en ? 'Previous' : 'Trước',
            'next' => $en ? 'Next' : 'Sau',
            'plan_code' => $en ? 'Plan code' : 'Mã gói',
            'name_vi' => $en ? 'Vietnamese name' : 'Tên tiếng Việt',
            'name_en' => $en ? 'English name' : 'Tên tiếng Anh',
            'price_vnd' => $en ? 'Price in VND' : 'Giá VND',
            'quota' => $en ? 'Quota' : 'Số lượt',
            'quota_placeholder' => $en ? 'Leave empty for unlimited' : 'Để trống nếu không giới hạn',
            'quota_period' => $en ? 'Period' : 'Chu kỳ',
            'store_type' => $en ? 'Store type' : 'Loại store',
            'default_plan' => $en ? 'Default plan' : 'Gói mặc định',
            'yes' => $en ? 'Yes' : 'Có',
            'no' => $en ? 'No' : 'Không',
            'apple_product_id' => $en ? 'Apple product ID' : 'Apple product ID',
            'google_product_id' => $en ? 'Google product ID' : 'Google product ID',
            'description_vi' => $en ? 'Vietnamese description' : 'Mô tả tiếng Việt',
            'description_en' => $en ? 'English description' : 'Mô tả tiếng Anh',
            'period_lifetime' => $en ? 'Lifetime' : 'Trọn đời',
            'period_week' => $en ? 'Week' : 'Tuần',
            'period_month' => $en ? 'Month' : 'Tháng',
            'period_unlimited' => $en ? 'Unlimited' : 'Không giới hạn',
            'store_free' => $en ? 'Free' : 'Miễn phí',
            'store_subscription' => $en ? 'Subscription' : 'Đăng ký định kỳ',
            'store_non_consumable' => $en ? 'Non-consumable' : 'Mua một lần',
            'content_saved' => $en ? 'Website content has been updated.' : 'Đã cập nhật nội dung website.',
            'content_saved_hint' => $en ? 'Landing and privacy pages read these values from MySQL.' : 'Landing page và privacy đang đọc nội dung từ MySQL.',
            'content_groups' => [
                'Common' => $en ? 'Common' : 'Dùng chung',
                'Landing' => $en ? 'Landing page' : 'Landing page',
                'Privacy' => $en ? 'Privacy page' : 'Trang quyền riêng tư',
            ],
            'vietnamese_content' => $en ? 'Vietnamese content' : 'Nội dung tiếng Việt',
            'english_content' => $en ? 'English content' : 'Nội dung tiếng Anh',
        ];
    }
}
