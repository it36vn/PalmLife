<?php

namespace Tests\Feature;

use App\Models\AnalysisRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PalmReadingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_palm_image_and_receive_reading_without_ai_wording(): void
    {
        config(['services.ai.provider' => 'ollama']);
        Http::fake(fn () => throw new ConnectionException('Ollama is not running.'));

        $user = User::factory()->create([
            'name' => 'Nguyen Van A',
            'birth_date' => '1995-05-20',
            'gender' => 'male',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/palm-readings', [
                'type' => 'palm',
                'locale' => 'vi',
                'image' => UploadedFile::fake()->image('palm.jpg', 900, 1200),
                'use_profile' => '1',
                'disclaimer_acknowledged' => '1',
            ]);

        $response->assertCreated()
            ->assertJsonPath('analysis.type', 'palm')
            ->assertJsonPath('analysis.result.provider', 'local-template');

        $result = $response->json('analysis.result');
        $this->assertIsArray($result);
        $this->assertSame('Bản đọc chỉ tay tham khảo', $result['title']);
        $this->assertGreaterThanOrEqual(6, count($result['sections']));

        $json = json_encode($result, JSON_UNESCAPED_UNICODE);
        $this->assertStringContainsString('Kiểu bàn tay', $json);
        $this->assertStringContainsString('Đường sinh đạo', $json);
        $this->assertStringContainsString('Đường trí đạo', $json);
        $this->assertStringContainsString('Đường tâm đạo', $json);
        $this->assertStringContainsString('vận mệnh', mb_strtolower($json));
        $this->assertStringContainsString('Tài vận', $json);
        $this->assertStringContainsString('hợp', mb_strtolower($json));
        $this->assertStringContainsString('xung', mb_strtolower($json));
        $this->assertDoesNotMatchRegularExpression('/(?<![A-Za-z])AI(?![A-Za-z])|trí tuệ nhân tạo|mô hình AI/u', $json);

        $this->assertDatabaseHas('analysis_requests', [
            'user_id' => $user->id,
            'type' => 'palm',
            'locale' => 'vi',
        ]);
    }

    public function test_ollama_reading_response_is_sanitized_before_returning_to_flutter(): void
    {
        config(['services.ai.provider' => 'ollama']);
        Http::fake([
            '127.0.0.1:11434/api/generate' => Http::response([
                'response' => json_encode([
                    'title' => 'Bản đọc AI',
                    'summary' => 'AI nhìn thấy lòng bàn tay rõ.',
                    'sections' => [
                        ['heading' => 'Đường sinh đạo', 'body' => 'Mô hình AI nhận thấy đường sinh đạo rõ.'],
                        ['heading' => 'Đường trí đạo', 'body' => 'Trí tuệ nhân tạo gợi ý bạn suy nghĩ kỹ.'],
                        ['heading' => 'Đường tâm đạo', 'body' => 'Cảm xúc chân thành và giao tiếp mềm.'],
                        ['heading' => 'Vận mệnh', 'body' => 'Hướng đi ổn định.'],
                        ['heading' => 'Tài vận', 'body' => 'Tích luỹ đều.'],
                        ['heading' => 'Hợp và xung', 'body' => 'Hợp người rõ ràng, xung người thất thường.'],
                        ['heading' => 'Tổng quan', 'body' => 'Bản đọc chỉ dùng cho giải trí.'],
                    ],
                    'safety_notice' => 'AI không đưa lời khuyên y tế.',
                ], JSON_UNESCAPED_UNICODE),
                'done' => true,
            ]),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/palm-readings', [
                'type' => 'palm',
                'locale' => 'vi',
                'image' => UploadedFile::fake()->image('palm.jpg', 900, 1200),
                'disclaimer_acknowledged' => '1',
            ]);

        $response->assertCreated()
            ->assertJsonPath('analysis.result.provider', 'ollama-vision');

        $json = json_encode($response->json('analysis.result'), JSON_UNESCAPED_UNICODE);
        $this->assertDoesNotMatchRegularExpression('/(?<![A-Za-z])AI(?![A-Za-z])|trí tuệ nhân tạo|mô hình AI/u', $json);
        $this->assertStringContainsString('Đường sinh đạo', $json);
        $this->assertStringContainsString('Đường trí đạo', $json);
        $this->assertStringContainsString('Đường tâm đạo', $json);
    }

    public function test_user_can_delete_only_their_own_palm_reading_history_item(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownAnalysis = AnalysisRequest::query()->create([
            'user_id' => $user->id,
            'type' => 'palm',
            'locale' => 'vi',
            'input_hash' => 'own-hash',
            'result' => ['title' => 'Bản đọc chỉ tay'],
            'disclaimer_acknowledged_at' => now(),
        ]);
        $otherAnalysis = AnalysisRequest::query()->create([
            'user_id' => $otherUser->id,
            'type' => 'palm',
            'locale' => 'vi',
            'input_hash' => 'other-hash',
            'result' => ['title' => 'Bản đọc chỉ tay'],
            'disclaimer_acknowledged_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/palm-readings/{$otherAnalysis->id}")
            ->assertNotFound();

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/palm-readings/{$ownAnalysis->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Deleted.');

        $this->assertDatabaseMissing('analysis_requests', ['id' => $ownAnalysis->id]);
        $this->assertDatabaseHas('analysis_requests', ['id' => $otherAnalysis->id]);
    }
}
