<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$request->user()->id],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:female,male,other'],
        ]);

        $request->user()->update($data);

        return response()->json(['user' => $request->user()->fresh()]);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
            'confirm_delete' => ['accepted'],
            'locale' => ['nullable', 'in:vi,en'],
        ]);

        $user = $request->user();
        $isEn = ($data['locale'] ?? 'vi') === 'en';

        if ($user->delete_account_disabled_until && $user->delete_account_disabled_until->isFuture()) {
            $date = $user->delete_account_disabled_until
                ->timezone(config('app.timezone'))
                ->format('d/m/Y H:i');

            throw ValidationException::withMessages([
                'password' => $isEn
                    ? "Account deletion is disabled until {$date} because the password was entered incorrectly too many times."
                    : "Chức năng xoá tài khoản bị vô hiệu đến {$date} vì bạn nhập sai mật khẩu quá nhiều lần.",
            ]);
        }

        if (! Hash::check($data['password'], $user->password)) {
            $attempts = min(5, ((int) $user->delete_account_failed_attempts) + 1);
            $disabledUntil = $attempts >= 5 ? now()->addDays(30) : null;

            $user->forceFill([
                'delete_account_failed_attempts' => $attempts,
                'delete_account_disabled_until' => $disabledUntil,
            ])->save();

            if ($disabledUntil) {
                throw ValidationException::withMessages([
                    'password' => $isEn
                        ? 'The password is incorrect. Account deletion is disabled for 30 days.'
                        : 'Mật khẩu không đúng. Chức năng xoá tài khoản bị vô hiệu trong 30 ngày.',
                ]);
            }

            $remaining = 5 - $attempts;

            throw ValidationException::withMessages([
                'password' => $isEn
                    ? "The password is incorrect. {$remaining} attempts remaining."
                    : "Mật khẩu không đúng. Bạn còn {$remaining} lần thử.",
            ]);
        }

        $user->forceFill([
            'delete_account_failed_attempts' => 0,
            'delete_account_disabled_until' => null,
        ])->save();

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Account and related app data deleted.']);
    }
}
