<?php

namespace Tests\Feature;

use App\Events\HubSpotSyncCompleted;
use App\Events\LeadImportedFromHubSpot;
use App\Jobs\SyncHubSpotContacts;
use App\Models\Integration;
use App\Models\Lead;
use App\Models\SyncCursor;
use App\Models\User;
use App\Services\HubSpot\HubSpotContactImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class HubSpotBackgroundSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Integration $integration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->integration = Integration::factory()->create([
            'user_id' => $this->user->id,
            'provider' => 'hubspot',
            'is_active' => true,
            'access_token' => 'test-token',
            'refresh_token' => 'test-refresh',
        ]);
    }

    public function test_imports_new_contacts_from_hubspot(): void
    {
        Event::fake([LeadImportedFromHubSpot::class]);

        Http::fake([
            'api.hubapi.com/crm/v3/objects/contacts*' => Http::response([
                'results' => [
                    [
                        'id' => '123',
                        'properties' => [
                            'firstname' => 'John',
                            'lastname' => 'Doe',
                            'email' => 'john@example.com',
                            'phone' => '555-1234',
                            'company' => 'Acme Inc',
                            'jobtitle' => 'CEO',
                            'createdate' => '2025-01-15T10:00:00Z',
                            'lastmodifieddate' => '2025-01-15T10:00:00Z',
                        ],
                    ],
                ],
                'paging' => null,
            ], 200),
        ]);

        $service = app(HubSpotContactImportService::class);
        $result = $service->importNewContacts($this->integration);

        $this->assertEquals(1, $result['imported']);
        $this->assertEquals(0, $result['updated']);
        $this->assertEquals(0, $result['skipped']);

        $this->assertDatabaseHas('leads', [
            'external_id' => '123',
            'source' => 'hubspot',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

        Event::assertDispatched(LeadImportedFromHubSpot::class);
    }

    public function test_updates_existing_leads_from_hubspot(): void
    {
        // Create existing lead
        Lead::factory()->create([
            'user_id' => $this->user->id,
            'external_id' => '123',
            'source' => 'hubspot',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'company' => 'Old Company',
        ]);

        Http::fake([
            'api.hubapi.com/crm/v3/objects/contacts*' => Http::response([
                'results' => [
                    [
                        'id' => '123',
                        'properties' => [
                            'firstname' => 'John',
                            'lastname' => 'Doe',
                            'email' => 'john@example.com',
                            'company' => 'New Company',
                            'jobtitle' => 'CTO',
                            'lastmodifieddate' => '2025-01-16T10:00:00Z',
                        ],
                    ],
                ],
                'paging' => null,
            ], 200),
        ]);

        $service = app(HubSpotContactImportService::class);
        $result = $service->importNewContacts($this->integration);

        $this->assertEquals(0, $result['imported']);
        $this->assertEquals(1, $result['updated']);

        $this->assertDatabaseHas('leads', [
            'external_id' => '123',
            'company' => 'New Company',
            'job_title' => 'CTO',
        ]);
    }

    public function test_sync_cursor_is_persisted(): void
    {
        Http::fake([
            'api.hubapi.com/crm/v3/objects/contacts*' => Http::response([
                'results' => [
                    [
                        'id' => '123',
                        'properties' => [
                            'firstname' => 'John',
                            'lastname' => 'Doe',
                            'email' => 'john@example.com',
                            'lastmodifieddate' => '2025-01-15T10:00:00Z',
                        ],
                    ],
                ],
                'paging' => [
                    'next' => ['after' => 'cursor-abc123'],
                ],
            ], 200),
        ]);

        $service = app(HubSpotContactImportService::class);
        $service->importNewContacts($this->integration);

        $this->assertDatabaseHas('sync_cursors', [
            'integration_id' => $this->integration->id,
            'cursor_type' => 'contacts',
            'cursor_value' => 'cursor-abc123',
        ]);

        $cursor = SyncCursor::where('integration_id', $this->integration->id)
            ->where('cursor_type', 'contacts')
            ->first();

        $this->assertNotNull($cursor->last_sync_at);
        $this->assertEquals(1, $cursor->records_synced);
    }

    public function test_uses_existing_cursor_for_incremental_sync(): void
    {
        // Create existing cursor
        SyncCursor::create([
            'integration_id' => $this->integration->id,
            'cursor_type' => 'contacts',
            'last_sync_at' => now()->subHour(),
            'cursor_value' => null,
            'records_synced' => 10,
        ]);

        Http::fake([
            'api.hubapi.com/crm/v3/objects/contacts*' => Http::sequence()
                ->push([
                    'results' => [],
                    'paging' => null,
                ], 200),
        ]);

        $service = app(HubSpotContactImportService::class);
        $service->importNewContacts($this->integration);

        // Verify filter was applied (checking the request URL would be ideal,
        // but we can verify by checking cursor was updated)
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'lastmodifieddate');
        });
    }

    public function test_job_processes_all_active_hubspot_integrations(): void
    {
        Event::fake([HubSpotSyncCompleted::class]);

        // Create another user with HubSpot integration
        $user2 = User::factory()->create();
        Integration::factory()->create([
            'user_id' => $user2->id,
            'provider' => 'hubspot',
            'is_active' => true,
            'access_token' => 'test-token-2',
        ]);

        // Create third user with inactive integration (should be skipped)
        $user3 = User::factory()->create();
        Integration::factory()->create([
            'user_id' => $user3->id,
            'provider' => 'hubspot',
            'is_active' => false,
        ]);

        // Create Gmail integration for original user (should be skipped)
        Integration::factory()->create([
            'user_id' => $this->user->id,
            'provider' => 'gmail',
            'is_active' => true,
        ]);

        Http::fake([
            'api.hubapi.com/*' => Http::response([
                'results' => [],
                'paging' => null,
            ], 200),
        ]);

        $job = new SyncHubSpotContacts();
        $job->handle(app(HubSpotContactImportService::class));

        Event::assertDispatched(HubSpotSyncCompleted::class, function ($event) {
            return $event->integrationsProcessed === 2;
        });
    }

    public function test_handles_api_errors_gracefully(): void
    {
        Http::fake([
            'api.hubapi.com/crm/v3/objects/contacts*' => Http::response([
                'message' => 'Rate limit exceeded',
            ], 429),
        ]);

        $service = app(HubSpotContactImportService::class);
        $result = $service->importNewContacts($this->integration);

        $this->assertEquals(0, $result['imported']);
        $this->assertArrayHasKey('error', $result);
    }

    public function test_sync_is_idempotent(): void
    {
        Http::fake([
            'api.hubapi.com/crm/v3/objects/contacts*' => Http::response([
                'results' => [
                    [
                        'id' => '123',
                        'properties' => [
                            'firstname' => 'John',
                            'lastname' => 'Doe',
                            'email' => 'john@example.com',
                            'lastmodifieddate' => '2025-01-15T10:00:00Z',
                        ],
                    ],
                ],
                'paging' => null,
            ], 200),
        ]);

        $service = app(HubSpotContactImportService::class);

        // Run sync twice
        $result1 = $service->importNewContacts($this->integration);
        $result2 = $service->importNewContacts($this->integration);

        // First run should import, second should update
        $this->assertEquals(1, $result1['imported']);
        $this->assertEquals(0, $result2['imported']);
        $this->assertEquals(1, $result2['updated']);

        // Only one lead should exist
        $this->assertEquals(1, Lead::where('external_id', '123')->count());
    }

    public function test_artisan_command_triggers_sync(): void
    {
        Http::fake([
            'api.hubapi.com/*' => Http::response([
                'results' => [],
                'paging' => null,
            ], 200),
        ]);

        $this->artisan('hubspot:sync')
            ->assertSuccessful()
            ->expectsOutputToContain('HubSpot sync completed');
    }
}
