<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnthropicClient
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const DEFAULT_MODEL = 'claude-sonnet-4-20250514';

    public function __construct(
        private ?string $apiKey = null,
        private string $model = self::DEFAULT_MODEL
    ) {
        $this->apiKey = $apiKey ?? config('services.anthropic.api_key');
    }

    public function sendMessage(string $prompt, int $maxTokens = 1024): ?array
    {
        if (!$this->apiKey) {
            Log::warning('Anthropic API key not configured');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(30)->post(self::API_URL, [
                'model' => $this->model,
                'max_tokens' => $maxTokens,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            ]);

            if ($response->failed()) {
                Log::error('Anthropic API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Anthropic API exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public function extractJsonFromResponse(?array $response): ?array
    {
        if (!$response) {
            return null;
        }

        $text = $response['content'][0]['text'] ?? '';

        // Try to extract JSON from the response
        if (preg_match('/\{[\s\S]*\}/m', $text, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return null;
    }

    public function getModel(): string
    {
        return $this->model;
    }
}
