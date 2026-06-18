<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StoreWebhookService;
use Illuminate\Http\Request;

class StoreWebhookController extends Controller
{
    public function apple(Request $request, StoreWebhookService $webhooks)
    {
        $webhooks->handleApple($request->all());

        return response()->json(['ok' => true]);
    }

    public function google(Request $request, StoreWebhookService $webhooks)
    {
        $webhooks->handleGoogle($request->all());

        return response()->json(['ok' => true]);
    }
}
