<?php

namespace App\Services\HubSpot;

use App\Events\LeadImportedFromHubSpot;
use App\Models\Integration;
use App\Models\Lead;
use App\Models\SyncCursor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HubSpotContactImportService
{
    private const BASE_URL = 'https://api.hubapi.com';
    private const CONTACTS_ENDPOINT = '/crm/v3/objects/contacts';

    private const CONTACT_PROPERTIES = [
        'firstname', 'lastname', 'email', 'phone', 'company', 'jobtitle',
        'website', 'lifecyclestage', 'hs_lead_status', 'hubspot_owner_id',
        'industry', 'annualrevenue', 'numberofemployees', 'city', 'state',
        'country', 'linkedin_url', 'createdate', 'lastmodifieddate',
    ];

    public function __construct(
        private HubSpotApiClient $apiClient
    ) {}

    /**
     * Import new/modified contacts from HubSpot.
     */
    public function importNewContacts(Integration $integration, int $limit = 100): array
    {
        $cursor = SyncCursor::getOrCreate($integration, 'contacts');
        $result = [
            'imported' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        try {
            $contacts = $this->fetchContacts($integration, $cursor, $limit);

            if (isset($contacts['error'])) {
                return array_merge($result, ['error' => $contacts['error']]);
            }

            foreach ($contacts['results'] as $hubspotContact) {
                $importResult = $this->importContact($hubspotContact, $integration->user_id);

                match ($importResult['status']) {
                    'imported' => $result['imported']++,
                    'updated' => $result['updated']++,
                    'skipped' => $result['skipped']++,
                    default => $result['errors']++,
                };
            }

            // Update cursor
            $nextCursor = $contacts['paging']['next']['after'] ?? null;
            $cursor->updateAfterSync($nextCursor, count($contacts['results']));

            Log::info('HubSpot contact sync completed', [
                'integration_id' => $integration->id,
                'imported' => $result['imported'],
                'updated' => $result['updated'],
            ]);

        } catch (\Exception $e) {
            Log::error('HubSpot contact sync failed', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
            ]);
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Fetch contacts from HubSpot API with filters.
     */
    private function fetchContacts(Integration $integration, SyncCursor $cursor, int $limit): array
    {
        $params = [
            'limit' => min($limit, 100),
            'properties' => implode(',', self::CONTACT_PROPERTIES),
        ];

        // Use cursor for pagination if available
        if ($cursor->cursor_value) {
            $params['after'] = $cursor->cursor_value;
        }

        // Filter by last modified date for incremental sync
        if ($cursor->last_sync_at) {
            $filterDate = $cursor->last_sync_at->timestamp * 1000; // HubSpot uses milliseconds
            $params['filterGroups'] = json_encode([
                [
                    'filters' => [
                        [
                            'propertyName' => 'lastmodifieddate',
                            'operator' => 'GTE',
                            'value' => $filterDate,
                        ],
                    ],
                ],
            ]);
        }

        $response = Http::withToken($integration->access_token)
            ->get(self::BASE_URL . self::CONTACTS_ENDPOINT, $params);

        if ($response->status() === 401) {
            $this->apiClient->refreshToken($integration);
            return $this->fetchContacts($integration, $cursor, $limit);
        }

        if ($response->failed()) {
            return ['error' => 'API request failed: ' . $response->status(), 'results' => []];
        }

        return $response->json();
    }

    /**
     * Import or update a single contact as a Lead.
     */
    public function importContact(array $hubspotContact, int $userId): array
    {
        $contactId = $hubspotContact['id'];
        $properties = $hubspotContact['properties'] ?? [];

        // Skip contacts without email (they're not useful as leads)
        if (empty($properties['email'])) {
            return ['status' => 'skipped', 'reason' => 'no_email'];
        }

        $leadData = $this->mapContactToLead($properties, $contactId, $userId);

        $existingLead = Lead::where('external_id', $contactId)
            ->where('source', 'hubspot')
            ->first();

        if ($existingLead) {
            $existingLead->update($leadData);
            event(new LeadImportedFromHubSpot($existingLead, isNew: false));
            return ['status' => 'updated', 'lead' => $existingLead];
        }

        $lead = Lead::create($leadData);
        event(new LeadImportedFromHubSpot($lead, isNew: true));

        return ['status' => 'imported', 'lead' => $lead];
    }

    /**
     * Map HubSpot contact properties to Lead model fields.
     */
    private function mapContactToLead(array $properties, string $contactId, int $userId): array
    {
        return [
            'user_id' => $userId,
            'external_id' => $contactId,
            'source' => 'hubspot',
            'first_name' => $properties['firstname'] ?? '',
            'last_name' => $properties['lastname'] ?? '',
            'email' => $properties['email'] ?? null,
            'phone' => $properties['phone'] ?? null,
            'company' => $properties['company'] ?? null,
            'job_title' => $properties['jobtitle'] ?? null,
            'website' => $properties['website'] ?? null,
            'linkedin_url' => $properties['linkedin_url'] ?? null,
            'status' => $this->mapLifecycleStage($properties['lifecyclestage'] ?? null),
            'tags' => $this->buildTags($properties),
        ];
    }

    /**
     * Map HubSpot lifecycle stage to our lead status.
     */
    private function mapLifecycleStage(?string $stage): string
    {
        return match ($stage) {
            'subscriber', 'lead' => 'new',
            'marketingqualifiedlead' => 'contacted',
            'salesqualifiedlead' => 'qualified',
            'opportunity' => 'proposal',
            'customer' => 'won',
            'evangelist' => 'won',
            'other' => 'new',
            default => 'new',
        };
    }

    /**
     * Build tags array from HubSpot properties.
     */
    private function buildTags(array $properties): array
    {
        $tags = ['hubspot-import'];

        if (!empty($properties['industry'])) {
            $tags[] = 'industry:' . $properties['industry'];
        }

        if (!empty($properties['lifecyclestage'])) {
            $tags[] = 'lifecycle:' . $properties['lifecyclestage'];
        }

        return $tags;
    }
}
