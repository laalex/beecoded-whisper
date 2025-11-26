<?php

namespace App\Services\HubSpot;

use App\Models\Integration;
use Illuminate\Support\Facades\Http;

class HubSpotApiClient
{
    private const BASE_URL = 'https://api.hubapi.com';

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
