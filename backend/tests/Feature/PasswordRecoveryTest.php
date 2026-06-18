<?php

namespace Tests\Feature;

use App\Mail\TemporaryPasswordMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_sends_temporary_password_and_allows_change(): void
    {
        Mail::fake();
        $user = User::factory()->create([
            'email' => 'reader@example.com',
            'password' => Hash::make('OldPass1!'),
        ]);

        $this->postJson('/api/password/forgot', [
            'email' => $user->email,
            'locale' => 'vi',
        ])->assertOk();

        $temporaryPassword = null;
        Mail::assertSent(TemporaryPasswordMail::class, function (TemporaryPasswordMail $mail) use (&$temporaryPassword, $user): bool {
            $temporaryPassword = $mail->temporaryPassword;

            return $mail->hasTo($user->email)
                && strlen($temporaryPassword) === 12
                && preg_match('/[A-Z]/', $temporaryPassword)
                && preg_match('/[a-z]/', $temporaryPassword)
                && preg_match('/\d/', $temporaryPassword)
                && preg_match('/[^\w\s]/', $temporaryPassword)
                && ! preg_match('/\s/', $temporaryPassword);
        });

        $this->assertTrue($user->fresh()->temporary_password_expires_at->isFuture());

        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $temporaryPassword,
        ])->assertOk();

        $token = $login->json('token');

        $this->withToken($token)->postJson('/api/password/change', [
            'current_password' => $temporaryPassword,
            'password' => 'weak password',
        ])->assertUnprocessable();

        $this->withToken($token)->postJson('/api/password/change', [
            'current_password' => $temporaryPassword,
            'password' => 'BetterPass1!',
        ])->assertOk();

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'BetterPass1!',
        ])->assertOk();

        $this->assertNull($user->fresh()->temporary_password_expires_at);
    }

    public function test_temporary_password_expires_after_one_minute(): void
    {
        Mail::fake();
        $user = User::factory()->create([
            'email' => 'expired@example.com',
            'password' => Hash::make('OldPass1!'),
        ]);

        $this->postJson('/api/password/forgot', [
            'email' => $user->email,
            'locale' => 'vi',
        ])->assertOk();

        $temporaryPassword = null;
        Mail::assertSent(TemporaryPasswordMail::class, function (TemporaryPasswordMail $mail) use (&$temporaryPassword): bool {
            $temporaryPassword = $mail->temporaryPassword;

            return true;
        });

        $this->travel(61)->seconds();

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $temporaryPassword,
        ])->assertUnprocessable();
    }

    public function test_locked_user_cannot_login_and_receives_support_contacts(): void
    {
        $user = User::factory()->create([
            'email' => 'locked@example.com',
            'password' => Hash::make('OldPass1!'),
            'locked_at' => now(),
        ]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'OldPass1!',
        ])
            ->assertStatus(423)
            ->assertJson([
                'code' => 'account_locked',
                'support_email' => config('app.support_email'),
                'support_phone' => config('app.support_phone'),
            ]);
    }
}
