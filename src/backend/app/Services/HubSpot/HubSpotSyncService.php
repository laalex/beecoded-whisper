<?php

namespace App\Services\HubSpot;

use App\Models\EnrichmentData;
use App\Models\Integration;
use App\Models\Lead;
use Illuminate\Support\Facades\Log;

class HubSpotSyncService
{
    public function __construct(
        private HubSpotApiClient $apiClient
    ) {}

    public function syncLeadIfStale(Lead $lead, int $staleMinutes = 30): ?EnrichmentData
    {
        if (!$lead->external_id || $lead->source !== 'hubspot') {
            return null;
        }

        $enrichment = $lead->enrichmentData;

        if ($enrichment && !$enrichment->isStale($staleMinutes)) {
            return $enrichment;
        }

        return $this->syncLead($lead);
    }

    public function syncLead(Lead $lead): ?EnrichmentData
    {
        if (!$lead->external_id) {
            return null;
        }

        $integration = $this->getHubSpotIntegration($lead->user_id);

        if (!$integration) {
            return $this->markSyncError($lead, 'No HubSpot integration found');
        }

        try {
            $contact = $this->apiClient->fetchContact($integration, $lead->external_id);

            if (!$contact) {
                return $this->markSyncError($lead, 'Contact not found in HubSpot');
            }

            return $this->updateEnrichmentData($lead, $contact, $integration);
        } catch (\Exception $e) {
            Log::error('HubSpot sync failed', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage()
            ]);
            return $this->markSyncError($lead, $e->getMessage());
        }
    }

    private function getHubSpotIntegration(int $userId): ?Integration
    {
        return Integration::where('user_id', $userId)
            ->where('provider', 'hubspot')
            ->where('is_active', true)
            ->first();
    }

    private function updateEnrichmentData(Lead $lead, array $contact, Integration $integration): EnrichmentData
    {
        $props = $contact['properties'] ?? [];
        $associations = $contact['associations'] ?? [];

        // Fetch associated deals
        $dealIds = collect($associations['deals']['results'] ?? [])->pluck('id')->toArray();
        $deals = $this->apiClient->fetchDeals($integration, $dealIds);

        // Fetch recent activities
        $activities = $this->apiClient->fetchRecentActivities($integration, $lead->external_id);

        // Fetch owner info
        $owner = $this->apiClient->fetchOwner($integration, $props['hubspot_owner_id'] ?? null);

        // Update lead fields from HubSpot
        $lead->update([
            'first_name' => $props['firstname'] ?? $lead->first_name,
            'last_name' => $props['lastname'] ?? $lead->last_name,
            'email' => $props['email'] ?? $lead->email,
            'phone' => $props['phone'] ?? $lead->phone,
            'company' => $props['company'] ?? $lead->company,
            'job_title' => $props['jobtitle'] ?? $lead->job_title,
            'website' => $props['website'] ?? $lead->website,
            'linkedin_url' => $props['linkedin_url'] ?? $lead->linkedin_url,
        ]);

        return EnrichmentData::updateOrCreate(
            ['lead_id' => $lead->id],
            [
                'provider' => 'hubspot',
                'company_data' => [
                    'name' => $props['company'] ?? null,
                    'city' => $props['city'] ?? null,
                    'state' => $props['state'] ?? null,
                    'country' => $props['country'] ?? null,
                ],
                'contact_data' => [
                    'hs_lead_status' => $props['hs_lead_status'] ?? null,
                    'createdate' => $props['createdate'] ?? null,
                    'lastmodifieddate' => $props['lastmodifieddate'] ?? null,
                ],
                'industry' => $props['industry'] ?? null,
                'employee_count' => $props['numberofemployees'] ?? null,
                'annual_revenue' => $props['annualrevenue'] ?? null,
                'hubspot_lifecycle_stage' => $props['lifecyclestage'] ?? null,
                'hubspot_deals' => $this->formatDeals($deals),
                'hubspot_activities' => $this->formatActivities($activities),
                'hubspot_owner' => $owner,
                'last_synced_at' => now(),
                'sync_error' => null,
                'enriched_at' => now(),
            ]
        );
    }

    private function formatDeals(array $deals): array
    {
        return array_map(fn($d) => [
            'id' => $d['id'] ?? null,
            'name' => $d['properties']['dealname'] ?? null,
            'amount' => $d['properties']['amount'] ?? null,
            'stage' => $d['properties']['dealstage'] ?? null,
            'close_date' => $d['properties']['closedate'] ?? null,
        ], $deals);
    }

    private function formatActivities(array $activities): array
    {
        return array_map(fn($a) => [
            'type' => $a['type'] ?? null,
            'timestamp' => $a['timestamp'] ?? null,
        ], array_slice($activities, 0, 10));
    }

    private function markSyncError(Lead $lead, string $error): ?EnrichmentData
    {
        $enrichment = $lead->enrichmentData;

        if ($enrichment) {
            $enrichment->update(['sync_error' => $error]);
            return $enrichment;
        }

        return EnrichmentData::create([
            'lead_id' => $lead->id,
            'provider' => 'hubspot',
            'sync_error' => $error,
        ]);
    }
}
