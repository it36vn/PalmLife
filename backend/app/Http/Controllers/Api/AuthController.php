<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\TemporaryPasswordMail;
use App\Models\ConsentRecord;
use App\Models\User;
use App\Services\PasswordPolicy;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request, SubscriptionService $subscriptions)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => PasswordPolicy::rules(),
            'locale' => ['nullable', 'in:vi,en'],
            'accepted_terms' => ['accepted'],
            'accepted_privacy' => ['accepted'],
        ]);

        $user = User::create($data);
        $subscriptions->ensureDefaultSubscription($user);

        foreach (['terms', 'privacy', 'ai_disclaimer'] as $purpose) {
            ConsentRecord::create([
                'user_id' => $user->id,
                'locale' => $data['locale'] ?? 'vi',
                'purpose' => $purpose,
                'accepted_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $this->tokenResponse($user, $subscriptions);
    }

    public function login(Request $request, SubscriptionService $subscriptions)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        if ($user->isLocked()) {
            return response()->json([
                'code' => 'account_locked',
                'message' => 'Tài khoản bị khoá. Vui lòng liên hệ bộ phận hỗ trợ.',
                'support_email' => config('app.support_email'),
                'support_phone' => config('app.support_phone'),
            ], 423);
        }

        if ($user->temporary_password_expires_at !== null && $user->temporary_password_expires_at->isPast()) {
            throw ValidationException::withMessages([
                'password' => 'Mật khẩu tạm thời đã hết hạn. Vui lòng yêu cầu mật khẩu mới.',
            ]);
        }

        $subscriptions->ensureDefaultSubscription($user);

        return $this->tokenResponse($user, $subscriptions);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function forgotPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'locale' => ['nullable', 'in:vi,en'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if ($user) {
            $temporaryPassword = PasswordPolicy::temporaryPassword();
            $user->forceFill([
                'password' => Hash::make($temporaryPassword),
                'temporary_password_expires_at' => now()->addMinute(),
            ])->save();

            Mail::to($user->email)->send(new TemporaryPasswordMail(
                user: $user,
                temporaryPassword: $temporaryPassword,
                changePasswordUrl: url('/change-password?email='.urlencode($user->email)),
                appStoreUrl: config('app.app_store_url'),
                googlePlayUrl: config('app.google_play_url'),
            ));
        }

        return response()->json([
            'message' => ($data['locale'] ?? 'vi') === 'en'
                ? 'If this email exists, a temporary password valid for 1 minute has been sent.'
                : 'Nếu email tồn tại, mật khẩu tạm thời có hiệu lực trong 1 phút đã được gửi.',
        ]);
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => PasswordPolicy::rules(),
        ]);

        if (! Hash::check($data['current_password'], $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Mật khẩu hiện tại không đúng.',
            ]);
        }

        $request->user()->forceFill([
            'password' => Hash::make($data['password']),
            'temporary_password_expires_at' => null,
        ])->save();

        return response()->json(['message' => 'Đã đổi mật khẩu.']);
    }

    public function me(Request $request, SubscriptionService $subscriptions)
    {
        return response()->json([
            'user' => $request->user(),
            'quota' => $subscriptions->quotaStatus($request->user()),
            'notifications' => $request->user()->notifications()
                ->whereNull('read_at')
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    private function tokenResponse(User $user, SubscriptionService $subscriptions)
    {
        return response()->json([
            'token' => $user->createToken('mobile')->plainTextToken,
            'user' => $user,
            'quota' => $subscriptions->quotaStatus($user),
            'notifications' => $user->notifications()
                ->whereNull('read_at')
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }
}
