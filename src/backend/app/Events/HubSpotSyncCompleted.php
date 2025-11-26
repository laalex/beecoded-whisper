<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HubSpotSyncCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $integrationsProcessed,
        public int $totalImported,
        public int $totalUpdated,
        public int $totalErrors
    ) {}
}
