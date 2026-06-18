<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Models\SubscriptionPlan;
use App\Models\WebsiteContent;
use Illuminate\Support\Facades\Route;

Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.store');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::prefix('admin')->name('admin.')->middleware('admin')->group(function (): void {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/{user}/toggle-lock', [AdminController::class, 'toggleUserLock'])->name('users.toggle-lock');
    Route::get('/analyses', [AdminController::class, 'analyses'])->name('analyses');
    Route::get('/notifications', [AdminController::class, 'notifications'])->name('notifications');
    Route::post('/notifications', [AdminController::class, 'storeNotification'])->name('notifications.store');
    Route::get('/plans', [AdminController::class, 'plans'])->name('plans');
    Route::post('/plans', [AdminController::class, 'storePlan'])->name('plans.store');
    Route::put('/plans/{plan}', [AdminController::class, 'updatePlan'])->name('plans.update');
    Route::delete('/plans/{plan}', [AdminController::class, 'destroyPlan'])->name('plans.destroy');
    Route::get('/contents', [AdminController::class, 'contents'])->name('contents.index');
    Route::post('/contents', [AdminController::class, 'updateContents'])->name('contents.update');
});

Route::get('/change-password', function () {
    $email = request('email', '');
    $deepLink = 'xemchitay:///change-password'.($email ? '?email='.urlencode((string) $email) : '');

    return view('change-password-bridge', [
        'deepLink' => $deepLink,
        'appStoreUrl' => config('app.app_store_url'),
        'googlePlayUrl' => config('app.google_play_url'),
    ]);
});

Route::get('/privacy/{locale?}', function (?string $locale = 'vi') {
    $locale = $locale === 'en' ? 'en' : 'vi';

    return view('privacy', [
        'locale' => $locale,
        'content' => WebsiteContent::valuesFor($locale),
    ]);
})->whereIn('locale', ['vi', 'en']);

Route::get('/{locale?}', function (?string $locale = 'vi') {
    $locale = $locale === 'en' ? 'en' : 'vi';
    SubscriptionPlan::syncDefaults();

    return view('palm-life', [
        'locale' => $locale,
        'content' => WebsiteContent::valuesFor($locale),
        'plans' => SubscriptionPlan::orderBy('price_vnd')->get(),
    ]);
})->whereIn('locale', ['vi', 'en']);
