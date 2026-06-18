<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function showLogin(): View
    {
        $locale = $this->adminLocale(request());

        return view('admin-login', [
            'adminLocale' => $locale,
            'tr' => $this->translations($locale),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $password = (string) config('app.admin_password');
        if ($password !== '' && hash_equals($password, $data['password'])) {
            $request->session()->put('admin_authenticated', true);

            return redirect()->intended('/admin');
        }

        $locale = $this->adminLocale($request);

        return back()
            ->withErrors(['password' => $locale === 'en' ? 'Invalid admin password.' : 'Mật khẩu admin không đúng.'])
            ->onlyInput();
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_authenticated');

        return redirect('/admin/login');
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
            'language' => $en ? 'Language' : 'Ngôn ngữ',
            'vietnamese' => 'Tiếng Việt',
            'english' => 'English',
            'back_to_landing' => $en ? 'Back to landing' : 'Quay lại landing',
            'password' => $en ? 'Admin password' : 'Mật khẩu admin',
            'login_title' => $en ? 'Admin console' : 'Quản trị',
            'login_intro' => $en ? 'Sign in to manage data, plans, and reading history.' : 'Đăng nhập để quản trị dữ liệu, gói dịch vụ và lịch sử xem.',
            'login_button' => $en ? 'Sign in' : 'Đăng nhập',
        ];
    }
}
