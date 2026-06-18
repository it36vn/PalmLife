<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AnalysisController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LegalController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\StorePurchaseController;
use App\Http\Controllers\Api\StoreWebhookController;
use App\Http\Controllers\Api\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/legal', [LegalController::class, 'show']);
Route::get('/subscriptions', [SubscriptionController::class, 'index']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
Route::post('/store/webhooks/apple', [StoreWebhookController::class, 'apple']);
Route::post('/store/webhooks/google', [StoreWebhookController::class, 'google']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/password/change', [AuthController::class, 'changePassword']);
    Route::put('/account', [AccountController::class, 'update']);
    Route::delete('/account', [AccountController::class, 'destroy']);
    Route::post('/subscriptions/checkout', [SubscriptionController::class, 'checkout']);
    Route::post('/subscriptions/store/verify', [StorePurchaseController::class, 'verify']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::get('/palm-readings', [AnalysisController::class, 'index']);
    Route::post('/palm-readings', [AnalysisController::class, 'store']);
    Route::get('/palm-readings/{analysis}', [AnalysisController::class, 'show']);
    Route::delete('/palm-readings/{analysis}', [AnalysisController::class, 'destroy']);
    Route::get('/analysis', [AnalysisController::class, 'index']);
    Route::post('/analysis', [AnalysisController::class, 'store']);
});
