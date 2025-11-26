<?php

namespace App\Console\Commands;

use App\Jobs\SyncHubSpotContacts;
use App\Models\Integration;
use App\Services\HubSpot\HubSpotContactImportService;
use Illuminate\Console\Command;

class SyncHubSpotCommand extends Command
{
    protected $signature = 'hubspot:sync
                            {--integration= : Specific integration ID to sync}
                            {--sync : Run synchronously instead of queuing}';

    protected $description = 'Sync contacts from HubSpot integrations';

    public function handle(HubSpotContactImportService $importService): int
    {
        $integrationId = $this->option('integration');

        if ($integrationId) {
            $integration = Integration::find($integrationId);
            if (!$integration || $integration->provider !== 'hubspot') {
                $this->error('Invalid HubSpot integration ID');
                return Command::FAILURE;
            }
        }

        if ($this->option('sync')) {
            $this->info('Running HubSpot sync synchronously...');
            $job = new SyncHubSpotContacts($integrationId);
            $job->handle($importService);
        } else {
            $this->info('Dispatching HubSpot sync job...');
            SyncHubSpotContacts::dispatch($integrationId);
        }

        $this->info('HubSpot sync completed');

        return Command::SUCCESS;
    }
}
