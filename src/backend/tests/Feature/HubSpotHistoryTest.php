<?php

namespace Tests\Feature;

use App\Models\EnrichmentData;
use App\Models\Integration;
use App\Models\Lead;
use App\Models\User;
use App\Services\HubSpot\HubSpotApiClient;
use App\Services\HubSpot\LeadHistoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HubSpotHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;
    private Lead $lead;
    private Integration $integration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('auth-token')->plainTextToken;

        $this->integration = Integration::factory()->create([
            'user_id' => $this->user->id,
            'provider' => 'hubspot',
            'is_active' => true,
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'token_expires_at' => now()->addHour(),
        ]);

        $this->lead = Lead::factory()->create([
            'user_id' => $this->user->id,
            'source' => 'hubspot',
            'external_id' => '12345',
        ]);
    }

    public function test_fetch_hubspot_history_endpoint_requires_auth(): void
    {
        $response = $this->getJson('/api/leads/' . $this->lead->id . '/hubspot-history');
        $response->assertStatus(401);
    }

    public function test_fetch_hubspot_history_returns_engagements(): void
    {
        Http::fake([
            'api.hubapi.com/crm/v3/objects/contacts/*/associations/engagements' => Http::response([
                'results' => [
                    ['id' => '100'],
                    ['id' => '101'],
                    ['id' => '102'],
                ],
            ]),
            'api.hubapi.com/engagements/v1/engagements/*' => Http::sequence()
                ->push([
                    'engagement' => [
                        'id' => '100',
                        'type' => 'EMAIL',
                        'timestamp' => now()->subDays(1)->timestamp * 1000,
                    ],
                    'metadata' => [
                        'subject' => 'Follow up meeting',
                        'from' => ['email' => 'sales@company.com'],
                        'to' => [['email' => 'lead@example.com']],
                    ],
                ])
                ->push([
                    'engagement' => [
                        'id' => '101',
                        'type' => 'CALL',
                        'timestamp' => now()->subDays(2)->timestamp * 1000,
                    ],
                    'metadata' => [
                        'body' => 'Discovery call notes',
                        'durationMilliseconds' => 1800000,
                        'status' => 'COMPLETED',
                    ],
                ])
                ->push([
                    'engagement' => [
                        'id' => '102',
                        'type' => 'MEETING',
                        'timestamp' => now()->subDays(3)->timestamp * 1000,
                    ],
                    'metadata' => [
                        'title' => 'Product Demo',
                        'body' => 'Demo meeting notes',
                    ],
                ]),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/leads/' . $this->lead->id . '/hubspot-history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'engagements' => [
                    '*' => [
                        'id',
                        'type',
                        'timestamp',
                        'metadata',
                    ],
                ],
                'summary' => [
                    'total',
                    'by_type',
                ],
            ]);
    }

    public function test_fetch_hubspot_history_requires_hubspot_integration(): void
    {
        $this->integration->delete();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/leads/' . $this->lead->id . '/hubspot-history');

        $response->assertStatus(400)
            ->assertJson(['message' => 'No active HubSpot integration found']);
    }

    public function test_fetch_hubspot_history_requires_external_id(): void
    {
        $leadWithoutExternalId = Lead::factory()->create([
            'user_id' => $this->user->id,
            'source' => 'manual',
            'external_id' => null,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/leads/' . $leadWithoutExternalId->id . '/hubspot-history');

        $response->assertStatus(400)
            ->assertJson(['message' => 'Lead is not linked to HubSpot']);
    }

    public function test_analyze_history_endpoint_requires_auth(): void
    {
        $response = $this->postJson('/api/leads/' . $this->lead->id . '/analyze-history');
        $response->assertStatus(401);
    }

    public function test_analyze_history_creates_analysis(): void
    {
        // Create enrichment data with HubSpot history
        EnrichmentData::factory()->create([
            'lead_id' => $this->lead->id,
            'provider' => 'hubspot',
            'hubspot_activities' => [
                [
                    'id' => '100',
                    'type' => 'EMAIL',
                    'timestamp' => now()->subDays(1)->timestamp,
                    'metadata' => ['subject' => 'Follow up'],
                ],
                [
                    'id' => '101',
                    'type' => 'CALL',
                    'timestamp' => now()->subDays(5)->timestamp,
                    'metadata' => ['body' => 'Call notes'],
                ],
            ],
        ]);

        // Mock both HubSpot and Anthropic APIs
        Http::fake([
            // HubSpot API calls (for fresh history fetch)
            'api.hubapi.com/crm/v3/objects/contacts/*/associations/engagements' => Http::response([
                'results' => [
                    ['id' => '100'],
                    ['id' => '101'],
                ],
            ]),
            'api.hubapi.com/engagements/v1/engagements/*' => Http::response([
                'engagement' => [
                    'id' => '100',
                    'type' => 'EMAIL',
                    'timestamp' => now()->subDays(1)->timestamp * 1000,
                ],
                'metadata' => ['subject' => 'Follow up'],
            ]),
            // Anthropic API
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    [
                        'type' => 'text',
                        'text' => json_encode([
                            'history_summary' => [
                                'total_engagements' => 2,
                                'time_span_days' => 5,
                                'engagement_types_breakdown' => [
                                    'email' => 1,
                                    'call' => 1,
                                ],
                            ],
                            'communication_patterns' => [
                                'preferred_channel' => 'email',
                                'response_pattern' => 'Quick responder',
                            ],
                            'buying_signals' => [
                                ['signal' => 'Asked about pricing', 'strength' => 'strong'],
                            ],
                            'next_best_actions' => [
                                [
                                    'action' => 'Send proposal',
                                    'priority' => 'high',
                                    'rationale' => 'Lead is highly engaged',
                                ],
                            ],
                            'deal_prediction' => [
                                'likelihood_to_close' => 75,
                                'confidence' => 80,
                            ],
                        ]),
                    ],
                ],
            ]),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/leads/' . $this->lead->id . '/analyze-history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'lead_id',
                'analysis_type',
                'insights',
                'recommendations',
                'confidence_score',
            ]);

        $this->assertDatabaseHas('ai_analyses', [
            'lead_id' => $this->lead->id,
            'analysis_type' => 'history',
        ]);
    }

    public function test_get_history_analysis_returns_latest(): void
    {
        EnrichmentData::factory()->create([
            'lead_id' => $this->lead->id,
            'provider' => 'hubspot',
            'hubspot_activities' => [],
        ]);

        // Mock both HubSpot and Anthropic APIs
        Http::fake([
            // HubSpot API calls (for fresh history fetch)
            'api.hubapi.com/crm/v3/objects/contacts/*/associations/engagements' => Http::response([
                'results' => [],
            ]),
            // Anthropic API
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    [
                        'type' => 'text',
                        'text' => json_encode([
                            'history_summary' => ['total_engagements' => 0],
                            'communication_patterns' => [],
                            'buying_signals' => [],
                            'next_best_actions' => [],
                            'deal_prediction' => ['likelihood_to_close' => 50, 'confidence' => 60],
                        ]),
                    ],
                ],
            ]),
        ]);

        // First create an analysis
        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/leads/' . $this->lead->id . '/analyze-history');

        // Then retrieve it
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/leads/' . $this->lead->id . '/history-analysis');

        $response->assertStatus(200)
            ->assertJsonPath('analysis_type', 'history');
    }

    public function test_get_history_analysis_returns_404_if_none(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/leads/' . $this->lead->id . '/history-analysis');

        $response->assertStatus(404);
    }

    public function test_user_cannot_access_other_users_lead_history(): void
    {
        $otherUser = User::factory()->create();
        $otherLead = Lead::factory()->create([
            'user_id' => $otherUser->id,
            'source' => 'hubspot',
            'external_id' => '99999',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/leads/' . $otherLead->id . '/hubspot-history');

        $response->assertStatus(403);
    }

    public function test_history_endpoint_paginates_large_results(): void
    {
        // Create 150 fake engagement IDs
        $engagementIds = array_map(fn($i) => ['id' => (string) $i], range(1, 150));

        Http::fake([
            'api.hubapi.com/crm/v3/objects/contacts/*/associations/engagements' => Http::response([
                'results' => $engagementIds,
            ]),
            'api.hubapi.com/engagements/v1/engagements/*' => Http::response([
                'engagement' => [
                    'id' => '1',
                    'type' => 'EMAIL',
                    'timestamp' => now()->timestamp * 1000,
                ],
                'metadata' => ['subject' => 'Test'],
            ]),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/leads/' . $this->lead->id . '/hubspot-history?limit=50');

        $response->assertStatus(200)
            ->assertJsonPath('summary.total', 150);
    }
}
