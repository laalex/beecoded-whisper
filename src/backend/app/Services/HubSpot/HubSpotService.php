<?php

namespace App\Services\HubSpot;

use App\Models\Integration;
use App\Models\Lead;
use Illuminate\Support\Facades\Http;

class HubSpotService
{
    private const BASE_URL = 'https://api.hubapi.com';

    public function syncContacts(Integration $integration): void
    {
        $contacts = $this->fetchContacts($integration);

        foreach ($contacts as $contact) {
            $this->createOrUpdateLead($integration, $contact);
        }
    }

    private function fetchContacts(Integration $integration): array
    {
        $response = Http::withToken($integration->access_token)
            ->get(self::BASE_URL . '/crm/v3/objects/contacts', [
                'limit' => 100,
                'properties' => ['firstname', 'lastname', 'email', 'phone', 'company', 'jobtitle', 'website'],
            ]);

        if ($response->failed()) {
            if ($response->status() === 401) {
                $this->refreshToken($integration);
                return $this->fetchContacts($integration);
            }
            throw new \Exception('Failed to fetch HubSpot contacts');
        }

        return $response->json('results', []);
    }

    private function createOrUpdateLead(Integration $integration, array $contact): void
    {
        $properties = $contact['properties'] ?? [];

        Lead::updateOrCreate(
            [
                'user_id' => $integration->user_id,
                'external_id' => $contact['id'],
                'source' => 'hubspot',
            ],
            [
                'first_name' => $properties['firstname'] ?? 'Unknown',
                'last_name' => $properties['lastname'] ?? null,
                'email' => $properties['email'] ?? null,
                'phone' => $properties['phone'] ?? null,
                'company' => $properties['company'] ?? null,
                'job_title' => $properties['jobtitle'] ?? null,
                'website' => $properties['website'] ?? null,
            ]
        );
    }

    private function refreshToken(Integration $integration): void
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

    public function pushLead(Integration $integration, Lead $lead): void
    {
        Http::withToken($integration->access_token)
            ->post(self::BASE_URL . '/crm/v3/objects/contacts', [
                'properties' => [
                    'firstname' => $lead->first_name,
                    'lastname' => $lead->last_name,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'company' => $lead->company,
                    'jobtitle' => $lead->job_title,
                    'website' => $lead->website,
                ],
            ]);
    }
}
