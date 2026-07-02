<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiOcrService
{
    public function analyze(string $imageBase64, string $mimeType): ?array
    {
        $apiKey = config('services.gemini.api_key');

        if (empty($apiKey)) {
            Log::error('GeminiOcrService: GEMINI_API_KEY chưa được cấu hình');
            throw new \RuntimeException('GEMINI_API_KEY chưa được cấu hình');
        }

        Log::info('GeminiOcrService: Gửi request đến Gemini API', [
            'mime_type' => $mimeType,
            'image_size_kb' => round(strlen($imageBase64) * 3 / 4 / 1024),
        ]);

        $response = Http::timeout(120)->withHeaders([
            'x-goog-api-key' => $apiKey,
            'Content-Type' => 'application/json',
            'Api-Revision' => '2026-05-20',
        ])->post('https://generativelanguage.googleapis.com/v1beta/interactions', [
            'model' => 'gemini-3.5-flash',
            'input' => [
                [
                    'type' => 'text',
                    'text' => 'Trích xuất thông tin từ hình ảnh biên bản vi phạm này và trả về JSON với cấu trúc: { "recorded_at": "12:15 13/03/2026", "location": "Tập đoàn ASG NB", "reporters": [{"ho_ten": "Nguyễn Đức Tuấn", "chuc_vu": "BV", "cong_ty": "ALPHA"}], "witnesses": [{"ho_ten": "...", "chuc_vu": "...", "cong_ty": "..."}], "violators": [{"ho_ten": "...", "chuc_vu": "...", "cong_ty": "..."}], "target": "", "violation_content": "nội dung vi phạm", "violation_count": "số lần", "violator_attitude": "thái độ", "resolution_direction": "hướng xử lý" }. Chỉ trả về JSON, không kèm giải thích.',
                ],
                [
                    'type' => 'image',
                    'data' => $imageBase64,
                    'mime_type' => $mimeType,
                ],
            ],
        ]);

        if ($response->failed()) {
            Log::error('GeminiOcrService: API lỗi', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Gemini API lỗi: ' . $response->body());
        }

        $data = $response->json();

        Log::info('GeminiOcrService: API response nhận được', [
            'status' => $data['status'] ?? 'unknown',
            'model' => $data['model'] ?? 'unknown',
            'steps_count' => count($data['steps'] ?? []),
        ]);

        $text = null;

        foreach ($data['steps'] ?? [] as $step) {
            if (($step['type'] ?? '') === 'model_output') {
                foreach ($step['content'] ?? [] as $content) {
                    if (($content['type'] ?? '') === 'text' && ! empty($content['text'])) {
                        $text = $content['text'];
                        break 2;
                    }
                }
            }
        }

        if (empty($text)) {
            Log::warning('GeminiOcrService: Không tìm thấy text trong response', [
                'keys' => array_keys($data),
            ]);
            return null;
        }

        Log::info('GeminiOcrService: Text trích xuất', [
            'text_preview' => mb_substr($text, 0, 500),
        ]);

        $json = $this->extractJson($text);

        if ($json === null) {
            Log::warning('GeminiOcrService: Không thể parse JSON từ text', [
                'text' => $text,
            ]);
            return null;
        }

        Log::info('GeminiOcrService: Parse JSON thành công');
        return $json;
    }

    private function extractJson(string $text): ?array
    {
        preg_match('/```json\s*([\s\S]*?)\s*```/', $text, $matches);

        if (! empty($matches[1])) {
            $text = $matches[1];
        }

        $decoded = json_decode($text, true);

        return is_array($decoded) ? $decoded : null;
    }
}
