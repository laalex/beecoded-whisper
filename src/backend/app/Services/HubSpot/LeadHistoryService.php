<?php

namespace App\Services\HubSpot;

use App\Models\EnrichmentData;
use App\Models\Integration;
use App\Models\Lead;
use Illuminate\Support\Facades\Log;

class LeadHistoryService
{
    public function __construct(
        private HubSpotApiClient $apiClient
    ) {}

    /**
     * Fetch complete lead history from HubSpot
     */
    public function fetchCompleteHistory(Lead $lead, int $limit = 100): array
    {
        if (!$lead->external_id) {
            throw new \InvalidArgumentException('Lead is not linked to HubSpot');
        }

        $integration = $this->getHubSpotIntegration($lead->user_id);
        if (!$integration) {
            throw new \RuntimeException('No active HubSpot integration found');
        }

        $historyData = $this->apiClient->fetchCompleteEngagementHistory(
            $integration,
            $lead->external_id,
            $limit
        );

        // Update enrichment data with full history
        $this->updateEnrichmentHistory($lead, $historyData);

        return [
            'engagements' => $historyData['engagements'],
            'summary' => $this->generateSummary($historyData['engagements'], $historyData['total_count']),
        ];
    }

    /**
     * Get cached history from enrichment data
     */
    public function getCachedHistory(Lead $lead): ?array
    {
        $enrichment = $lead->enrichmentData;
        if (!$enrichment || !$enrichment->hubspot_activities) {
            return null;
        }

        return [
            'engagements' => $enrichment->hubspot_activities,
            'summary' => $this->generateSummary(
                $enrichment->hubspot_activities,
                count($enrichment->hubspot_activities)
            ),
        ];
    }

    /**
     * Prepare history context for AI analysis
     */
    public function prepareHistoryForAnalysis(Lead $lead): array
    {
        $enrichment = $lead->enrichmentData;
        $activities = $enrichment?->hubspot_activities ?? [];

        if (empty($activities)) {
            return [
                'has_history' => false,
                'summary' => null,
                'timeline' => [],
                'patterns' => null,
            ];
        }

        $summary = $this->generateSummary($activities, count($activities));
        $timeline = $this->buildTimeline($activities);
        $patterns = $this->detectPatterns($activities);

        return [
            'has_history' => true,
            'summary' => $summary,
            'timeline' => $timeline,
            'patterns' => $patterns,
            'recent_engagements' => array_slice($activities, 0, 20),
        ];
    }

    private function getHubSpotIntegration(int $userId): ?Integration
    {
        return Integration::where('user_id', $userId)
            ->where('provider', 'hubspot')
            ->where('is_active', true)
            ->first();
    }

    private function updateEnrichmentHistory(Lead $lead, array $historyData): void
    {
        EnrichmentData::updateOrCreate(
            ['lead_id' => $lead->id],
            [
                'provider' => 'hubspot',
                'hubspot_activities' => $historyData['engagements'],
                'last_synced_at' => now(),
            ]
        );
    }

    private function generateSummary(array $engagements, int $totalCount): array
    {
        $byType = [];
        $timestamps = [];

        foreach ($engagements as $engagement) {
            $type = strtolower($engagement['type'] ?? 'unknown');
            $byType[$type] = ($byType[$type] ?? 0) + 1;

            if (isset($engagement['timestamp'])) {
                // Handle both Unix timestamp (int) and ISO date string
                $ts = $engagement['timestamp'];
                if (is_string($ts)) {
                    $ts = strtotime($ts);
                }
                if ($ts && is_numeric($ts)) {
                    $timestamps[] = (int) $ts;
                }
            }
        }

        $timeSpanDays = 0;
        if (count($timestamps) >= 2) {
            $timeSpanDays = (int) ((max($timestamps) - min($timestamps)) / 86400);
        }

        return [
            'total' => $totalCount,
            'fetched' => count($engagements),
            'by_type' => $byType,
            'time_span_days' => $timeSpanDays,
            'first_engagement' => !empty($timestamps) ? date('Y-m-d', min($timestamps)) : null,
            'last_engagement' => !empty($timestamps) ? date('Y-m-d', max($timestamps)) : null,
        ];
    }

    private function normalizeTimestamp($timestamp): ?int
    {
        if ($timestamp === null) {
            return null;
        }
        if (is_string($timestamp)) {
            $timestamp = strtotime($timestamp);
        }
        return is_numeric($timestamp) ? (int) $timestamp : null;
    }

    private function buildTimeline(array $activities): array
    {
        $timeline = [];
        $currentMonth = null;

        foreach ($activities as $activity) {
            $timestamp = $this->normalizeTimestamp($activity['timestamp'] ?? null);
            if (!$timestamp) {
                continue;
            }

            $month = date('Y-m', $timestamp);
            if ($month !== $currentMonth) {
                $currentMonth = $month;
                $timeline[] = [
                    'month' => $month,
                    'engagements' => [],
                ];
            }

            $timeline[count($timeline) - 1]['engagements'][] = [
                'type' => $activity['type'],
                'date' => date('Y-m-d', $timestamp),
                'summary' => $this->getEngagementSummary($activity),
            ];
        }

        return $timeline;
    }

    private function getEngagementSummary(array $activity): string
    {
        $type = $activity['type'] ?? 'Unknown';
        $metadata = $activity['metadata'] ?? [];

        return match ($type) {
            'EMAIL' => $metadata['subject'] ?? 'Email sent/received',
            'CALL' => sprintf(
                'Call (%s)',
                $metadata['status'] ?? 'completed'
            ),
            'MEETING' => $metadata['title'] ?? 'Meeting',
            'NOTE' => 'Note added',
            'TASK' => $metadata['subject'] ?? 'Task',
            default => $type,
        };
    }

    private function detectPatterns(array $activities): array
    {
        if (count($activities) < 3) {
            return [
                'preferred_channel' => null,
                'engagement_frequency' => 'insufficient_data',
                'most_active_day' => null,
                'most_active_hour' => null,
            ];
        }

        // Count by type
        $typeCounts = [];
        $dayCounts = [];
        $hourCounts = [];

        foreach ($activities as $activity) {
            $type = $activity['type'] ?? 'unknown';
            $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;

            $timestamp = $this->normalizeTimestamp($activity['timestamp'] ?? null);
            if ($timestamp) {
                $day = date('l', $timestamp); // Day name
                $hour = (int) date('G', $timestamp); // Hour 0-23
                $dayCounts[$day] = ($dayCounts[$day] ?? 0) + 1;
                $hourCounts[$hour] = ($hourCounts[$hour] ?? 0) + 1;
            }
        }

        // Find preferred channel
        arsort($typeCounts);
        $preferredChannel = array_key_first($typeCounts);

        // Find most active day
        arsort($dayCounts);
        $mostActiveDay = array_key_first($dayCounts);

        // Find most active hour range
        arsort($hourCounts);
        $mostActiveHour = array_key_first($hourCounts);
        $hourRange = match (true) {
            $mostActiveHour >= 6 && $mostActiveHour < 12 => 'morning',
            $mostActiveHour >= 12 && $mostActiveHour < 17 => 'afternoon',
            $mostActiveHour >= 17 && $mostActiveHour < 21 => 'evening',
            default => 'other',
        };

        // Calculate engagement frequency
        $timestamps = array_filter(array_map(
            fn($activity) => $this->normalizeTimestamp($activity['timestamp'] ?? null),
            $activities
        ));
        $frequency = 'low';
        if (count($timestamps) >= 2) {
            $timeSpan = max($timestamps) - min($timestamps);
            $avgDaysBetween = $timeSpan / (count($timestamps) - 1) / 86400;
            $frequency = match (true) {
                $avgDaysBetween <= 2 => 'very_high',
                $avgDaysBetween <= 7 => 'high',
                $avgDaysBetween <= 14 => 'medium',
                default => 'low',
            };
        }

        return [
            'preferred_channel' => strtolower($preferredChannel),
            'engagement_frequency' => $frequency,
            'most_active_day' => $mostActiveDay,
            'most_active_time' => $hourRange,
            'type_breakdown' => $typeCounts,
        ];
    }
}
