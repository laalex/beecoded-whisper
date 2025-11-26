<?php

namespace App\Listeners;

use App\Events\LeadImportedFromHubSpot;
use App\Services\AI\LeadAnalysisService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Attributes\ListensTo;
use Illuminate\Support\Facades\Log;

#[ListensTo(LeadImportedFromHubSpot::class)]
class ProcessNewHubSpotLead implements ShouldQueue
{
    public function __construct(
        private LeadAnalysisService $analysisService
    ) {}

    public function handle(LeadImportedFromHubSpot $event): void
    {
        // Only process newly imported leads
        if (!$event->isNew) {
            return;
        }

        $lead = $event->lead;

        Log::info('Processing new HubSpot lead', [
            'lead_id' => $lead->id,
            'email' => $lead->email,
        ]);

        // Run initial AI analysis on the new lead
        try {
            $this->analysisService->analyzeLead($lead);
            Log::info('Initial AI analysis completed for HubSpot lead', [
                'lead_id' => $lead->id,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to analyze new HubSpot lead', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Future: Add more automated actions here
        // - Auto-assign to sales rep based on territory
        // - Send welcome email
        // - Add to nurturing sequence
        // - Notify team via Slack/Discord
    }
}
