<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiOcrService
{
    public function analyze(string $imageBase64, string $mimeType): ?array
    {
        $apiKey = config('services.gemini.api_key');

        if (empty($apiKey)) {
            throw new \RuntimeException('GEMINI_API_KEY chưa được cấu hình');
        }

        $response = Http::withHeaders([
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
            throw new \RuntimeException('Gemini API lỗi: ' . $response->body());
        }

        $data = $response->json();

        $text = $data['output'][0]['text'] ?? null;

        if (empty($text)) {
            return null;
        }

        $json = $this->extractJson($text);

        if ($json === null) {
            return null;
        }

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
