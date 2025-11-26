<?php

namespace App\Services\HubSpot;

use App\Models\Integration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HubSpotApiClient
{
    private const BASE_URL = 'https://api.hubapi.com';

    /**
     * Fetch all engagement IDs for a contact
     */
    public function fetchAllEngagementIds(Integration $integration, string $contactId): array
    {
        $response = Http::withToken($integration->access_token)
            ->get(self::BASE_URL . "/crm/v3/objects/contacts/{$contactId}/associations/engagements");

        if ($response->status() === 401) {
            $this->refreshToken($integration);
            return $this->fetchAllEngagementIds($integration, $contactId);
        }

        if ($response->failed()) {
            Log::warning('Failed to fetch engagement IDs', [
                'contact_id' => $contactId,
                'status' => $response->status(),
            ]);
            return [];
        }

        return collect($response->json('results', []))
            ->pluck('id')
            ->toArray();
    }

    /**
     * Fetch complete engagement history with full details
     */
    public function fetchCompleteEngagementHistory(
        Integration $integration,
        string $contactId,
        int $limit = 100
    ): array {
        $engagementIds = $this->fetchAllEngagementIds($integration, $contactId);
        $limitedIds = array_slice($engagementIds, 0, $limit);

        $engagements = [];
        foreach ($limitedIds as $engagementId) {
            $engagement = $this->fetchEngagementDetail($integration, $engagementId);
            if ($engagement) {
                $engagements[] = $engagement;
            }
        }

        // Sort by timestamp descending
        usort($engagements, function ($a, $b) {
            return ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
        });

        return [
            'engagements' => $engagements,
            'total_count' => count($engagementIds),
            'fetched_count' => count($engagements),
        ];
    }

    /**
     * Fetch detailed engagement with metadata
     */
    public function fetchEngagementDetail(Integration $integration, string $engagementId): ?array
    {
        $response = Http::withToken($integration->access_token)
            ->get(self::BASE_URL . "/engagements/v1/engagements/{$engagementId}");

        if ($response->status() === 401) {
            $this->refreshToken($integration);
            return $this->fetchEngagementDetail($integration, $engagementId);
        }

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();
        $engagement = $data['engagement'] ?? [];
        $metadata = $data['metadata'] ?? [];

        return [
            'id' => $engagement['id'] ?? $engagementId,
            'type' => $engagement['type'] ?? 'UNKNOWN',
            'timestamp' => isset($engagement['timestamp'])
                ? (int) ($engagement['timestamp'] / 1000)
                : null,
            'created_at' => isset($engagement['createdAt'])
                ? date('c', $engagement['createdAt'] / 1000)
                : null,
            'metadata' => $this->formatEngagementMetadata($engagement['type'] ?? '', $metadata),
        ];
    }

    /**
     * Format engagement metadata based on type
     */
    private function formatEngagementMetadata(string $type, array $metadata): array
    {
        return match ($type) {
            'EMAIL' => [
                'subject' => $metadata['subject'] ?? null,
                'from' => $metadata['from']['email'] ?? null,
                'to' => collect($metadata['to'] ?? [])->pluck('email')->toArray(),
                'cc' => collect($metadata['cc'] ?? [])->pluck('email')->toArray(),
                'text' => $this->truncateText($metadata['text'] ?? $metadata['html'] ?? null, 500),
            ],
            'CALL' => [
                'body' => $this->truncateText($metadata['body'] ?? null, 500),
                'duration_seconds' => isset($metadata['durationMilliseconds'])
                    ? (int) ($metadata['durationMilliseconds'] / 1000)
                    : null,
                'status' => $metadata['status'] ?? null,
                'disposition' => $metadata['disposition'] ?? null,
                'to_number' => $metadata['toNumber'] ?? null,
            ],
            'MEETING' => [
                'title' => $metadata['title'] ?? null,
                'body' => $this->truncateText($metadata['body'] ?? null, 500),
                'start_time' => $metadata['startTime'] ?? null,
                'end_time' => $metadata['endTime'] ?? null,
            ],
            'NOTE' => [
                'body' => $this->truncateText($metadata['body'] ?? null, 500),
            ],
            'TASK' => [
                'subject' => $metadata['subject'] ?? null,
                'body' => $this->truncateText($metadata['body'] ?? null, 500),
                'status' => $metadata['status'] ?? null,
                'task_type' => $metadata['taskType'] ?? null,
            ],
            default => $metadata,
        };
    }

    private function truncateText(?string $text, int $length): ?string
    {
        if ($text === null) {
            return null;
        }
        // Strip HTML tags and truncate
        $text = strip_tags($text);
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . '...';
    }

    public function fetchContact(Integration $integration, string $contactId): ?array
    {
        $properties = [
            'firstname', 'lastname', 'email', 'phone', 'company', 'jobtitle',
            'website', 'lifecyclestage', 'hs_lead_status', 'hubspot_owner_id',
            'industry', 'annualrevenue', 'numberofemployees', 'city', 'state',
            'country', 'linkedin_url', 'createdate', 'lastmodifieddate',
            'notes_last_updated', 'num_associated_deals',
        ];

        $response = Http::withToken($integration->access_token)
            ->get(self::BASE_URL . "/crm/v3/objects/contacts/{$contactId}", [
                'properties' => implode(',', $properties),
                'associations' => 'deals',
            ]);

        if ($response->status() === 401) {
            $this->refreshToken($integration);
            return $this->fetchContact($integration, $contactId);
        }

        if ($response->failed()) {
            throw new \Exception('HubSpot API error: ' . $response->status());
        }

        return $response->json();
    }

    public function fetchDeals(Integration $integration, array $dealIds): array
    {
        if (empty($dealIds)) {
            return [];
        }

        $deals = [];
        foreach (array_slice($dealIds, 0, 5) as $dealId) {
            $response = Http::withToken($integration->access_token)
                ->get(self::BASE_URL . "/crm/v3/objects/deals/{$dealId}", [
                    'properties' => 'dealname,amount,dealstage,closedate,pipeline',
                ]);

            if ($response->successful()) {
                $deals[] = $response->json();
            }
        }

        return $deals;
    }

    public function fetchRecentActivities(Integration $integration, string $contactId): array
    {
        $response = Http::withToken($integration->access_token)
            ->get(self::BASE_URL . "/crm/v3/objects/contacts/{$contactId}/associations/engagements");

        if ($response->failed()) {
            return [];
        }

        $engagementIds = collect($response->json('results', []))
            ->pluck('id')
            ->take(10)
            ->toArray();

        $activities = [];
        foreach ($engagementIds as $engagementId) {
            $actResponse = Http::withToken($integration->access_token)
                ->get(self::BASE_URL . "/engagements/v1/engagements/{$engagementId}");

            if ($actResponse->successful()) {
                $activities[] = $actResponse->json('engagement');
            }
        }

        return $activities;
    }

    public function fetchOwner(Integration $integration, ?string $ownerId): ?array
    {
        if (!$ownerId) {
            return null;
        }

        $response = Http::withToken($integration->access_token)
            ->get(self::BASE_URL . "/crm/v3/owners/{$ownerId}");

        if ($response->failed()) {
            return null;
        }

        $owner = $response->json();
        return [
            'id' => $owner['id'] ?? null,
            'email' => $owner['email'] ?? null,
            'first_name' => $owner['firstName'] ?? null,
            'last_name' => $owner['lastName'] ?? null,
        ];
    }

    public function refreshToken(Integration $integration): void
    {
        $response = Http::asForm()->post(self::BASE_URL . '/oauth/v1/token', [
            'grant_type' => 'refresh_token',
            'client_id' => config('services.hubspot.client_id'),
            'client_secret' => config('services.hubspot.client_secret'),
            'refresh_token' => $integration->refresh_token,
        ]);

        if ($response->successful()) {
            $integration->update([
                'access_token' => $response->json('access_token'),
                'refresh_token' => $response->json('refresh_token'),
                'token_expires_at' => now()->addSeconds($response->json('expires_in')),
            ]);
        }
    }
}
