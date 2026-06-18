<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class AiReadingService
{
    public function analyze(string $type, string $locale, string $inputHash, string $imagePath, string $mimeType, array $profile = []): array
    {
        $isVietnamese = $locale !== 'en';
        $profileLabel = $this->profileLabel($profile, $isVietnamese);
        $provider = (string) config('services.ai.provider', 'ollama');

        if ($provider === 'ollama' || $provider === 'auto') {
            $reading = $this->analyzeWithOllamaVision($type, $locale, $imagePath, $profileLabel);
            if ($reading !== null) {
                return $reading + [
                    'provider' => 'ollama-vision',
                    'type' => $type,
                    'input_hash' => $inputHash,
                ];
            }
        }

        if (($provider === 'openai' || $provider === 'auto') && config('services.openai.api_key')) {
            $reading = $this->analyzeWithOpenAiVision($type, $locale, $imagePath, $mimeType, $profileLabel);
            if ($reading !== null) {
                return $reading + [
                    'provider' => 'openai-vision',
                    'type' => $type,
                    'input_hash' => $inputHash,
                ];
            }
        }

        return $this->fallbackReading($type, $inputHash, $profileLabel, $isVietnamese);
    }

    private function analyzeWithOllamaVision(string $type, string $locale, string $imagePath, string $profileLabel): ?array
    {
        $imageData = base64_encode((string) file_get_contents($imagePath));

        try {
            $response = Http::acceptJson()
                ->timeout(90)
                ->post(rtrim((string) config('services.ollama.base_url'), '/').'/api/generate', [
                    'model' => config('services.ollama.model'),
                    'prompt' => $this->prompt($type, $locale, $profileLabel),
                    'images' => [$imageData],
                    'stream' => false,
                    'format' => $this->responseSchema(),
                    'options' => [
                        'temperature' => 0.7,
                    ],
                ]);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }

        if (! $response->successful()) {
            report('Palm reading Ollama request failed: '.$response->status().' '.$response->body());

            return null;
        }

        return $this->parseReading((string) ($response->json('response') ?? ''), $locale);
    }

    private function analyzeWithOpenAiVision(string $type, string $locale, string $imagePath, string $mimeType, string $profileLabel): ?array
    {
        $imageData = base64_encode((string) file_get_contents($imagePath));
        $schema = $this->responseSchema();

        try {
            $response = Http::withToken((string) config('services.openai.api_key'))
                ->acceptJson()
                ->timeout(45)
                ->post(rtrim((string) config('services.openai.base_url'), '/').'/responses', [
                    'model' => config('services.openai.model'),
                    'input' => [[
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'input_text',
                                'text' => $this->prompt($type, $locale, $profileLabel),
                            ],
                            [
                                'type' => 'input_image',
                                'image_url' => "data:{$mimeType};base64,{$imageData}",
                            ],
                        ],
                    ]],
                    'text' => [
                        'format' => [
                            'type' => 'json_schema',
                            'name' => 'palm_reading',
                            'schema' => $schema,
                            'strict' => true,
                        ],
                    ],
                ]);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }

        if (! $response->successful()) {
            report('Palm reading vision request failed: '.$response->status().' '.$response->body());

            return null;
        }

        $payload = $response->json();
        $content = $this->extractOutputText($payload);
        if ($content === null) {
            return null;
        }

        return $this->parseReading($content, $locale);
    }

    private function parseReading(string $content, string $locale): ?array
    {
        $isVietnamese = $locale !== 'en';
        $reading = json_decode($content, true);
        if (! is_array($reading)) {
            return null;
        }

        return [
            'title' => $this->cleanText((string) ($reading['title'] ?? ($isVietnamese ? 'Bản đọc chỉ tay' : 'Palm reading'))),
            'summary' => $this->cleanText((string) ($reading['summary'] ?? '')),
            'sections' => collect($reading['sections'] ?? [])
                ->take(8)
                ->map(fn ($section): array => [
                    'heading' => $this->cleanText((string) ($section['heading'] ?? '')),
                    'body' => $this->cleanText((string) ($section['body'] ?? '')),
                ])
                ->filter(fn (array $section): bool => $section['heading'] !== '' && $section['body'] !== '')
                ->values()
                ->all(),
            'safety_notice' => $this->cleanText((string) ($reading['safety_notice'] ?? ($isVietnamese
                ? 'Nội dung chỉ dùng cho giải trí và tự phản chiếu.'
                : 'This content is for entertainment and self-reflection only.'))),
        ];
    }

    private function fallbackReading(string $type, string $inputHash, string $profileLabel, bool $isVietnamese): array
    {
        return [
            'provider' => 'local-template',
            'type' => $type,
            'title' => $isVietnamese ? 'Bản đọc chỉ tay tham khảo' : 'Reference palm reading',
            'summary' => $isVietnamese
                ? 'Nội dung được lập dựa trên ảnh bàn tay và thông tin bạn cung cấp. Đây là nội dung giải trí, không phải lời khuyên y tế, tài chính, pháp lý hoặc dự đoán chắc chắn.'
                : 'This reading is based on your palm image and the details you provided. It is for entertainment only, not medical, financial, legal advice, or a guaranteed prediction.',
            'sections' => [
                [
                    'heading' => $isVietnamese ? 'Kiểu bàn tay và khí chất' : 'Hand type and temperament',
                    'body' => $isVietnamese
                        ? 'Bàn tay được đọc theo nhóm khí chất như Thổ, Hoả, Khí hoặc Thuỷ. Với ảnh hiện tại, bản đọc nghiêng về người thực tế, nhạy cảm với cảm xúc xung quanh, có xu hướng quan sát kỹ rồi mới quyết định.'
                        : 'The hand is read through classic Earth, Fire, Air, or Water temperaments. This reading leans toward someone practical, sensitive to the room, and likely to observe carefully before deciding.',
                ],
                [
                    'heading' => $isVietnamese ? 'Đường sinh đạo' : 'Life line',
                    'body' => $isVietnamese
                        ? 'Sinh đạo đại diện cho sức bền, nền tảng gia đình và cách bạn phục hồi sau áp lực. Dáng đọc này cho thấy bạn hợp nhịp sống ổn định, càng có mục tiêu rõ càng bền bỉ.'
                        : 'The life line reflects stamina, grounding, and recovery style. This reading suggests you do best with steady rhythms and become more resilient when your goals are clear.',
                ],
                [
                    'heading' => $isVietnamese ? 'Đường trí đạo' : 'Head line',
                    'body' => $isVietnamese
                        ? 'Trí đạo nói về tư duy, học hỏi và cách xử lý vấn đề. Bạn có thiên hướng phân tích, nhưng khi đã tin ai hoặc tin điều gì thì khá kiên định; hợp việc cần tập trung và có lộ trình.'
                        : 'The head line points to thinking, learning, and problem solving. You appear analytical, yet once you trust someone or something, your mind can become firm; structured work suits you.',
                ],
                [
                    'heading' => $isVietnamese ? 'Đường tâm đạo' : 'Heart line',
                    'body' => $isVietnamese
                        ? 'Tâm đạo liên quan đến tình cảm, sự gắn bó và cách bộc lộ yêu thương. Bản đọc nghiêng về người chân thành, yêu chậm nhưng sâu; cần người biết lắng nghe và tôn trọng ranh giới.'
                        : 'The heart line relates to affection, attachment, and emotional expression. This points to sincerity, slower but deeper bonding, and a need for someone who listens and respects boundaries.',
                ],
                [
                    'heading' => $isVietnamese ? 'Đường vận mệnh và sự nghiệp' : 'Fate line and career path',
                    'body' => $isVietnamese
                        ? 'Vận mệnh trong chỉ tay được xem như mức độ ổn định của hướng đi. Bạn hợp phát triển theo từng giai đoạn, có quý nhân khi chủ động mở rộng quan hệ, nhưng xung với môi trường quá mơ hồ hoặc thay đổi thất thường.'
                        : 'In palm reading, the fate line reflects direction and outside influences. You suit gradual growth, attract support by widening your network, and clash with vague or constantly shifting environments.',
                ],
                [
                    'heading' => $isVietnamese ? 'Tài vận' : 'Wealth tendency',
                    'body' => $isVietnamese
                        ? 'Tài vận nghiêng về tích luỹ đều hơn là bùng nổ nhanh. Hợp các việc cần kỹ năng, uy tín và sự bền bỉ; nên tránh quyết định tiền bạc vì nể nang hoặc vì muốn chứng minh bản thân.'
                        : 'Your wealth tendency favors steady accumulation over sudden leaps. Skill, reputation, and consistency are favorable; avoid money decisions made from pressure or the urge to prove yourself.',
                ],
                [
                    'heading' => $isVietnamese ? 'Tình duyên, hợp và xung' : 'Love, harmony, and friction',
                    'body' => $isVietnamese
                        ? 'Bạn hợp người rõ ràng, điềm tĩnh, biết giữ lời và tôn trọng nhịp riêng. Dễ xung với người nóng nảy, kiểm soát quá mức hoặc hứa nhiều làm ít. Trong tình cảm, càng minh bạch càng bền.'
                        : 'You harmonize with calm, clear, reliable people who respect your pace. Friction appears with controlling, impulsive, or inconsistent partners. Clarity keeps love steadier.',
                ],
            ],
            'safety_notice' => $isVietnamese
                ? 'Nếu nội dung khiến bạn lo lắng, hãy bỏ qua và trao đổi với người có chuyên môn phù hợp.'
                : 'If this content makes you anxious, disregard it and talk to a qualified professional.',
            'input_hash' => $inputHash,
        ];
    }

    private function cleanText(string $text): string
    {
        $text = preg_replace('/mô hình\s+AI/iu', 'ứng dụng', $text) ?? $text;
        $text = preg_replace('/(?<![A-Za-z])AI(?![A-Za-z])/u', 'ứng dụng', $text) ?? $text;
        $text = preg_replace('/artificial intelligence/iu', 'the app', $text) ?? $text;
        $text = preg_replace('/trí tuệ nhân tạo/iu', 'ứng dụng', $text) ?? $text;

        return trim($text);
    }

    private function prompt(string $type, string $locale, string $profileLabel): string
    {
        if ($locale === 'en') {
            return <<<TEXT
You are a palm reading app. Inspect the uploaded palm image and write a friendly entertainment-only reading based on visible palm features.

Reference details: {$profileLabel}
Reading type: {$type}

Rules:
- Do not mention AI, model, algorithm, or image processing.
- If the image does not clearly show a palm, politely say the palm is not clear and give guidance to retake it.
- Discuss visible features such as palm shape, hand type, life line, head line, heart line, fate line, sun line, mounts, line clarity, finger proportions, and overall impression when visible.
- Write like a consumer palm reading app: include temperament/personality, love tendency, career path, wealth tendency, destiny/luck tendency, compatible traits, and friction/conflict traits.
- Use soft wording such as "suggests", "leans toward", "favors", and "may clash with"; do not present any prediction as certain.
- Keep the tone warm, but avoid fear, curses, guaranteed predictions, medical, legal, financial, hiring, credit, or spiritual advice.
- Include 6 to 8 sections with practical headings, not one generic paragraph.
- Return only JSON matching the schema.
TEXT;
        }

        return <<<TEXT
Bạn là ứng dụng xem chỉ tay. Hãy quan sát ảnh bàn tay được tải lên và viết bản đọc thân thiện, chỉ phục vụ giải trí, dựa trên các đặc điểm nhìn thấy trong ảnh.

Thông tin tham chiếu: {$profileLabel}
Kiểu xem: {$type}

Quy tắc:
- Không nhắc đến AI, mô hình, thuật toán hoặc xử lý ảnh.
- Nếu ảnh không thấy rõ lòng bàn tay, hãy nói nhẹ nhàng rằng ảnh chưa đủ rõ và hướng dẫn chụp lại.
- Khi nhìn thấy được, hãy diễn giải dáng bàn tay, kiểu tay theo Thổ/Hoả/Khí/Thuỷ, sinh đạo, trí đạo, tâm đạo, đường vận mệnh, đường thái dương, gò tay, độ rõ của đường chỉ, tỷ lệ ngón tay và ấn tượng tổng quan.
- Viết giống ứng dụng xem chỉ tay phổ thông: có tính cách/khí chất, tình duyên, sự nghiệp, tài vận, vận mệnh/may mắn, điểm hợp và điểm xung.
- Dùng ngôn ngữ mềm như "nghiêng về", "gợi ý", "hợp với", "dễ xung với"; không khẳng định chắc chắn tương lai.
- Văn phong ấm áp, nhưng không gieo sợ hãi, không nói lời nguyền, không dự đoán chắc chắn, không đưa lời khuyên y tế, pháp lý, tài chính, tuyển dụng, tín dụng hoặc tâm linh.
- Trả 6 đến 8 mục có tiêu đề thực tế, không gom thành một đoạn chung chung.
- Chỉ trả về JSON đúng schema.
TEXT;
    }

    private function responseSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['title', 'summary', 'sections', 'safety_notice'],
            'properties' => [
                'title' => ['type' => 'string'],
                'summary' => ['type' => 'string'],
                'sections' => [
                    'type' => 'array',
                    'minItems' => 6,
                    'maxItems' => 8,
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['heading', 'body'],
                        'properties' => [
                            'heading' => ['type' => 'string'],
                            'body' => ['type' => 'string'],
                        ],
                    ],
                ],
                'safety_notice' => ['type' => 'string'],
            ],
        ];
    }

    private function extractOutputText(array $payload): ?string
    {
        if (isset($payload['output_text']) && is_string($payload['output_text'])) {
            return $payload['output_text'];
        }

        foreach ($payload['output'] ?? [] as $output) {
            foreach ($output['content'] ?? [] as $content) {
                if (($content['type'] ?? null) === 'output_text' && isset($content['text'])) {
                    return (string) $content['text'];
                }
            }
        }

        return null;
    }

    private function profileLabel(array $profile, bool $isVietnamese): string
    {
        $name = trim((string) ($profile['name'] ?? ''));
        $birthDate = trim((string) ($profile['birth_date'] ?? ''));
        $gender = trim((string) ($profile['gender'] ?? ''));

        if ($name === '' && $birthDate === '' && $gender === '') {
            return $isVietnamese
                ? 'Bạn chưa cung cấp thông tin cá nhân, nên bản đọc tập trung vào ảnh bàn tay.'
                : 'No personal details were provided, so this reading focuses on the palm image.';
        }

        $genderLabel = match ($gender) {
            'female' => $isVietnamese ? 'nữ' : 'female',
            'male' => $isVietnamese ? 'nam' : 'male',
            'other' => $isVietnamese ? 'khác' : 'other',
            default => $gender,
        };

        $parts = [];
        if ($name !== '') {
            $parts[] = $name;
        }
        if ($birthDate !== '') {
            $parts[] = ($isVietnamese ? 'ngày sinh ' : 'birth date ').$birthDate;
        }
        if ($genderLabel !== '') {
            $parts[] = ($isVietnamese ? 'giới tính ' : 'gender ').$genderLabel;
        }

        return implode(', ', $parts).'.';
    }
}
