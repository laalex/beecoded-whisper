<?php

namespace App\Services\AI;

use App\Models\Transcription;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TranscriptionService
{
    public function transcribeAudio(UploadedFile $file, int $userId, ?int $leadId = null): Transcription
    {
        $path = $file->store('transcriptions', 'local');

        $transcription = Transcription::create([
            'user_id' => $userId,
            'lead_id' => $leadId,
            'type' => 'voice_note',
            'audio_file_path' => $path,
            'status' => 'processing',
        ]);

        try {
            $transcript = $this->callElevenLabsAPI($path);
            $summary = $this->summarizeWithClaude($transcript);
            $actionItems = $this->extractActionItems($transcript);

            $transcription->update([
                'transcript' => $transcript,
                'summary' => $summary['summary'],
                'action_items' => $actionItems,
                'key_points' => $summary['key_points'],
                'status' => 'completed',
            ]);
        } catch (\Exception $e) {
            $transcription->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        return $transcription;
    }

    private function callElevenLabsAPI(string $path): string
    {
        $fullPath = Storage::disk('local')->path($path);

        $response = Http::withHeaders([
            'xi-api-key' => config('services.elevenlabs.api_key'),
        ])
            ->attach('file', file_get_contents($fullPath), basename($path))
            ->post('https://api.elevenlabs.io/v1/speech-to-text', [
                'model_id' => 'scribe_v1',
                'language_code' => 'en',
            ]);

        if ($response->failed()) {
            throw new \Exception('Transcription failed: ' . $response->body());
        }

        return $response->json('text');
    }

    private function summarizeWithClaude(string $transcript): array
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.anthropic.api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 1024,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => "Analyze this sales call transcript and provide:
1. A brief summary (2-3 sentences)
2. Key points discussed (list of 3-5 items)

Transcript:
{$transcript}

Respond in JSON format:
{\"summary\": \"...\", \"key_points\": [\"point1\", \"point2\", ...]}"
                ]
            ]
        ]);

        if ($response->failed()) {
            return ['summary' => 'Unable to generate summary', 'key_points' => []];
        }

        $content = $response->json('content.0.text', '{}');
        return json_decode($content, true) ?? ['summary' => $content, 'key_points' => []];
    }

    private function extractActionItems(string $transcript): array
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.anthropic.api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 512,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => "Extract action items from this transcript. Return as JSON array of strings.

Transcript:
{$transcript}

Response format: [\"action1\", \"action2\", ...]"
                ]
            ]
        ]);

        if ($response->failed()) {
            return [];
        }

        $content = $response->json('content.0.text', '[]');
        return json_decode($content, true) ?? [];
    }

    public function processVoiceInput(string $audioData, int $userId, ?int $leadId = null): array
    {
        $tempPath = 'temp/' . uniqid() . '.webm';
        Storage::disk('local')->put($tempPath, base64_decode($audioData));

        try {
            $transcript = $this->callElevenLabsAPI($tempPath);

            $intent = $this->analyzeIntent($transcript);

            return [
                'transcript' => $transcript,
                'intent' => $intent,
                'success' => true,
            ];
        } finally {
            Storage::disk('local')->delete($tempPath);
        }
    }

    private function analyzeIntent(string $transcript): array
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.anthropic.api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 256,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => "Analyze this voice command for a sales CRM. Determine the intent and extract entities.

Input: \"{$transcript}\"

Possible intents: create_lead, update_lead, add_note, schedule_followup, search_lead, create_reminder

Response format:
{\"intent\": \"intent_name\", \"entities\": {\"field\": \"value\"}, \"confidence\": 0.95}"
                ]
            ]
        ]);

        if ($response->failed()) {
            return ['intent' => 'unknown', 'entities' => [], 'confidence' => 0];
        }

        $content = $response->json('content.0.text', '{}');
        return json_decode($content, true) ?? ['intent' => 'unknown', 'entities' => [], 'confidence' => 0];
    }
}
