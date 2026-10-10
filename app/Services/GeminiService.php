<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;

    protected string $model;

    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key') ?? '';
        $this->model = config('services.gemini.model', 'gemini-2.5-flash');
        $this->baseUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";
    }

    public function generateAnalytic(string $prompt): string
    {
        if (empty($this->apiKey)) {
            throw new \Exception('GEMINI_API_KEY tidak ditemukan di konfigurasi.');
        }

        $response = Http::timeout(20)->post("{$this->baseUrl}?key={$this->apiKey}", [
            'contents' => [['parts' => [['text' => $prompt]]]],
        ]);

        if ($response->status() === 429) {
            Log::warning('Gemini API rate limited: '.$response->body());
            throw new \Exception('QUOTA_EXCEEDED');
        }

        if ($response->failed()) {
            Log::error('Gemini API Response Error: '.$response->body());
            throw new \Exception('Gagal terhubung ke Gemini API: '.$response->status());
        }

        $data = $response->json();

        return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Tidak ada respon dari AI.';
    }
}
