<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalysisRequest;
use App\Services\AiReadingService;
use App\Services\SubscriptionService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    public function index(Request $request)
    {
        $analyses = $request->user()
            ->analysisRequests()
            ->latest()
            ->paginate(20);

        return response()->json([
            'analyses' => $analyses->items(),
            'pagination' => $this->pagination($analyses),
        ]);
    }

    public function show(Request $request, AnalysisRequest $analysis)
    {
        abort_unless($analysis->user_id === $request->user()->id, 404);

        return response()->json([
            'analysis' => $analysis,
        ]);
    }

    public function destroy(Request $request, AnalysisRequest $analysis)
    {
        abort_unless($analysis->user_id === $request->user()->id, 404);

        $analysis->delete();

        return response()->json([
            'message' => 'Deleted.',
        ]);
    }

    public function store(Request $request, SubscriptionService $subscriptions, AiReadingService $ai)
    {
        $data = $request->validate([
            'type' => ['required', 'in:palm,astrology,numerology,combined'],
            'locale' => ['nullable', 'in:vi,en'],
            'image' => ['required', 'image', 'max:8192'],
            'use_profile' => ['nullable', 'boolean'],
            'name' => ['nullable', 'string', 'max:120'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'gender' => ['nullable', 'in:female,male,other'],
            'disclaimer_acknowledged' => ['accepted'],
        ]);

        $quota = $subscriptions->quotaStatus($request->user());
        if (! $quota['allowed']) {
            return response()->json([
                'code' => 'quota_exhausted',
                'message' => ($data['locale'] ?? 'vi') === 'en'
                    ? 'Your current plan has no readings left. Please wait for your quota to reset or upgrade to a higher plan.'
                    : 'Gói hiện tại đã hết số lần xem. Vui lòng chờ đến khi làm mới lượt hoặc nâng cấp lên gói cao hơn.',
                'quota' => $quota,
            ], 402);
        }

        $hash = hash_file('sha256', $data['image']->getRealPath());
        $profile = $this->readingProfile($request, $data);
        $result = $ai->analyze(
            $data['type'],
            $data['locale'] ?? 'vi',
            $hash,
            $data['image']->getRealPath(),
            $data['image']->getMimeType() ?: 'image/jpeg',
            $profile,
        );

        $analysis = $request->user()->analysisRequests()->create([
            'type' => $data['type'],
            'locale' => $data['locale'] ?? 'vi',
            'input_hash' => $hash,
            'result' => $result,
            'disclaimer_acknowledged_at' => now(),
        ]);

        return response()->json([
            'analysis' => $analysis,
            'quota' => $subscriptions->quotaStatus($request->user()),
        ], 201);
    }

    private function pagination(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'has_more_pages' => $paginator->hasMorePages(),
        ];
    }

    private function readingProfile(Request $request, array $data): array
    {
        if (($data['use_profile'] ?? false) && $request->user()->birth_date !== null && $request->user()->gender !== null) {
            return [
                'name' => $request->user()->name,
                'birth_date' => $request->user()->birth_date?->toDateString(),
                'gender' => $request->user()->gender,
                'source' => 'account',
            ];
        }

        return [
            'name' => $data['name'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
            'gender' => $data['gender'] ?? null,
            'source' => 'request',
        ];
    }
}
