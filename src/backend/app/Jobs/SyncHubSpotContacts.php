<?php

namespace App\Jobs;

use App\Events\HubSpotSyncCompleted;
use App\Models\Integration;
use App\Services\HubSpot\HubSpotContactImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncHubSpotContacts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public ?int $integrationId = null
    ) {}

    public function handle(HubSpotContactImportService $importService): void
    {
        $query = Integration::where('provider', 'hubspot')
            ->where('is_active', true);

        // If specific integration ID provided, sync only that one
        if ($this->integrationId) {
            $query->where('id', $this->integrationId);
        }

        $integrations = $query->get();

        $totals = [
            'processed' => 0,
            'imported' => 0,
            'updated' => 0,
            'errors' => 0,
        ];

        foreach ($integrations as $integration) {
            Log::info('Starting HubSpot sync', ['integration_id' => $integration->id]);

            $result = $importService->importNewContacts($integration);

            $totals['processed']++;
            $totals['imported'] += $result['imported'] ?? 0;
            $totals['updated'] += $result['updated'] ?? 0;

            if (isset($result['error'])) {
                $totals['errors']++;
                Log::warning('HubSpot sync had errors', [
                    'integration_id' => $integration->id,
                    'error' => $result['error'],
                ]);
            }
        }

        event(new HubSpotSyncCompleted(
            integrationsProcessed: $totals['processed'],
            totalImported: $totals['imported'],
            totalUpdated: $totals['updated'],
            totalErrors: $totals['errors']
        ));

        Log::info('HubSpot background sync completed', $totals);
    }
}
