<?php

namespace App\Services\Gmail;

use App\Models\Integration;
use App\Models\Interaction;
use App\Models\Lead;
use Illuminate\Support\Facades\Http;

class GmailService
{
    private const BASE_URL = 'https://www.googleapis.com/gmail/v1';

    public function syncEmails(Integration $integration): void
    {
        $messages = $this->fetchMessages($integration);

        foreach ($messages as $message) {
            $this->processMessage($integration, $message['id']);
        }
    }

    private function fetchMessages(Integration $integration): array
    {
        $response = Http::withToken($integration->access_token)
            ->get(self::BASE_URL . '/users/me/messages', [
                'maxResults' => 50,
                'q' => 'newer_than:7d',
            ]);

        if ($response->failed()) {
            if ($response->status() === 401) {
                $this->refreshToken($integration);
                return $this->fetchMessages($integration);
            }
            return [];
        }

        return $response->json('messages', []);
    }

    private function processMessage(Integration $integration, string $messageId): void
    {
        $response = Http::withToken($integration->access_token)
            ->get(self::BASE_URL . "/users/me/messages/{$messageId}", [
                'format' => 'full',
            ]);

        if ($response->failed()) {
            return;
        }

        $message = $response->json();
        $headers = collect($message['payload']['headers'] ?? []);

        $from = $headers->firstWhere('name', 'From')['value'] ?? '';
        $to = $headers->firstWhere('name', 'To')['value'] ?? '';
        $subject = $headers->firstWhere('name', 'Subject')['value'] ?? '';
        $date = $headers->firstWhere('name', 'Date')['value'] ?? '';

        preg_match('/<(.+?)>/', $from, $fromMatches);
        $fromEmail = $fromMatches[1] ?? $from;

        $lead = Lead::where('user_id', $integration->user_id)
            ->where('email', $fromEmail)
            ->first();

        if ($lead) {
            Interaction::updateOrCreate(
                [
                    'external_id' => $messageId,
                    'lead_id' => $lead->id,
                ],
                [
                    'user_id' => $integration->user_id,
                    'type' => 'email',
                    'direction' => str_contains($from, $integration->provider_email) ? 'outbound' : 'inbound',
                    'subject' => $subject,
                    'content' => $this->getMessageBody($message),
                    'occurred_at' => $date ? new \DateTime($date) : now(),
                ]
            );
        }
    }

    private function getMessageBody(array $message): string
    {
        $parts = $message['payload']['parts'] ?? [];

        foreach ($parts as $part) {
            if ($part['mimeType'] === 'text/plain' && !empty($part['body']['data'])) {
                return base64_decode(strtr($part['body']['data'], '-_', '+/'));
            }
        }

        if (!empty($message['payload']['body']['data'])) {
            return base64_decode(strtr($message['payload']['body']['data'], '-_', '+/'));
        }

        return '';
    }

    private function refreshToken(Integration $integration): void
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'refresh_token',
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $integration->refresh_token,
        ]);

        if ($response->successful()) {
            $integration->update([
                'access_token' => $response->json('access_token'),
                'token_expires_at' => now()->addSeconds($response->json('expires_in')),
            ]);
        }
    }

    public function sendEmail(Integration $integration, string $to, string $subject, string $body): bool
    {
        $message = "To: {$to}\r\n";
        $message .= "Subject: {$subject}\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
        $message .= $body;

        $encodedMessage = rtrim(strtr(base64_encode($message), '+/', '-_'), '=');

        $response = Http::withToken($integration->access_token)
            ->post(self::BASE_URL . '/users/me/messages/send', [
                'raw' => $encodedMessage,
            ]);

        return $response->successful();
    }
}
