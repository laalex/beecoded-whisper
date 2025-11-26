<?php

namespace App\Services\AI;

use App\Models\AiAnalysis;
use App\Models\Lead;
use App\Services\HubSpot\LeadHistoryService;

class LeadHistoryAnalysisService
{
    public function __construct(
        private AnthropicClient $anthropic,
        private LeadHistoryService $historyService,
        private PromptLoader $promptLoader
    ) {}

    /**
     * Perform AI analysis on complete lead history
     */
    public function analyzeHistory(Lead $lead): ?AiAnalysis
    {
        // First, fetch fresh history from HubSpot if this is a HubSpot lead
        if ($lead->source === 'hubspot' && $lead->external_id) {
            try {
                $this->historyService->fetchCompleteHistory($lead, 100);
                $lead->refresh(); // Refresh to get updated enrichment data
            } catch (\Exception $e) {
                // Continue with existing data if fetch fails
            }
        }

        $historyContext = $this->historyService->prepareHistoryForAnalysis($lead);

        if (!$historyContext['has_history']) {
            return $this->createNoHistoryAnalysis($lead);
        }

        $context = $this->buildFullContext($lead, $historyContext);
        $prompt = $this->promptLoader->load('lead_history_analysis', ['context' => $context]);
        $maxTokens = $this->promptLoader->getMaxTokens('lead_history_analysis');

        $response = $this->anthropic->sendMessage($prompt, $maxTokens);
        $analysis = $this->anthropic->extractJsonFromResponse($response);

        if (!$analysis) {
            return $this->createFallbackAnalysis($lead, $historyContext);
        }

        return $this->saveAnalysis($lead, $analysis);
    }

    /**
     * Get existing history analysis if not stale
     */
    public function getAnalysisIfFresh(Lead $lead, int $staleMinutes = 60): ?AiAnalysis
    {
        $analysis = AiAnalysis::where('lead_id', $lead->id)
            ->where('analysis_type', 'history')
            ->latest('analyzed_at')
            ->first();

        if ($analysis && !$analysis->isStale($staleMinutes)) {
            return $analysis;
        }

        return null;
    }

    private function buildFullContext(Lead $lead, array $historyContext): array
    {
        $enrichment = $lead->enrichmentData;

        return [
            'lead' => [
                'name' => $lead->full_name,
                'email' => $lead->email,
                'company' => $lead->company,
                'job_title' => $lead->job_title,
                'status' => $lead->status,
                'score' => $lead->score,
                'estimated_value' => $lead->estimated_value,
                'source' => $lead->source,
                'created_at' => $lead->created_at->format('Y-m-d'),
            ],
            'history_summary' => $historyContext['summary'],
            'communication_patterns' => $historyContext['patterns'],
            'timeline' => $historyContext['timeline'],
            'recent_engagements' => $historyContext['recent_engagements'],
            'enrichment' => $enrichment ? [
                'industry' => $enrichment->industry,
                'employee_count' => $enrichment->employee_count,
                'annual_revenue' => $enrichment->annual_revenue,
                'hubspot_lifecycle' => $enrichment->hubspot_lifecycle_stage,
                'deals' => $enrichment->hubspot_deals,
            ] : null,
        ];
    }

    private function saveAnalysis(Lead $lead, array $analysis): AiAnalysis
    {
        return AiAnalysis::updateOrCreate(
            ['lead_id' => $lead->id, 'analysis_type' => 'history'],
            [
                'insights' => [
                    'history_summary' => $analysis['history_summary'] ?? null,
                    'communication_patterns' => $analysis['communication_patterns'] ?? null,
                    'relationship_timeline' => $analysis['relationship_timeline'] ?? null,
                    'key_topics_discussed' => $analysis['key_topics_discussed'] ?? [],
                    'buying_signals' => $analysis['buying_signals'] ?? [],
                    'objections_raised' => $analysis['objections_raised'] ?? [],
                    'deal_prediction' => $analysis['deal_prediction'] ?? null,
                    'insights' => $analysis['insights'] ?? null,
                ],
                'recommendations' => $analysis['next_best_actions'] ?? [],
                'risks' => $this->extractRisks($analysis),
                'opportunities' => $this->extractOpportunities($analysis),
                'confidence_score' => $analysis['deal_prediction']['confidence'] ?? 70.00,
                'model_used' => $this->anthropic->getModel(),
                'analyzed_at' => now(),
            ]
        );
    }

    private function extractRisks(array $analysis): array
    {
        $risks = [];

        // Extract from deal prediction risks
        foreach ($analysis['deal_prediction']['risks_to_close'] ?? [] as $risk) {
            $risks[] = [
                'factor' => 'deal_risk',
                'severity' => 'high',
                'description' => $risk,
                'mitigation' => null,
            ];
        }

        // Extract from objections
        foreach ($analysis['objections_raised'] ?? [] as $objection) {
            if (($objection['status'] ?? '') !== 'addressed') {
                $risks[] = [
                    'factor' => 'unresolved_objection',
                    'severity' => 'medium',
                    'description' => $objection['objection'] ?? 'Unknown objection',
                    'mitigation' => 'Address this objection in next interaction',
                ];
            }
        }

        return $risks;
    }

    private function extractOpportunities(array $analysis): array
    {
        $opportunities = [];

        // Extract from buying signals
        foreach ($analysis['buying_signals'] ?? [] as $signal) {
            if (($signal['strength'] ?? '') === 'strong') {
                $opportunities[] = [
                    'type' => 'buying_signal',
                    'description' => $signal['signal'] ?? 'Strong buying signal detected',
                    'potential_value' => 'high',
                ];
            }
        }

        return $opportunities;
    }

    private function createNoHistoryAnalysis(Lead $lead): AiAnalysis
    {
        return AiAnalysis::updateOrCreate(
            ['lead_id' => $lead->id, 'analysis_type' => 'history'],
            [
                'insights' => [
                    'history_summary' => [
                        'total_engagements' => 0,
                        'engagement_quality' => 'unknown',
                    ],
                    'insights' => [
                        'summary' => 'No engagement history available for analysis.',
                        'engagement_trend' => 'unknown',
                        'recommended_approach' => 'Sync HubSpot data to enable history analysis.',
                    ],
                ],
                'recommendations' => [
                    [
                        'action' => 'Sync lead data from HubSpot',
                        'priority' => 'high',
                        'optimal_timing' => 'immediate',
                        'rationale' => 'No history data available for analysis',
                    ],
                ],
                'risks' => [],
                'opportunities' => [],
                'confidence_score' => 20.00,
                'model_used' => 'no_data',
                'analyzed_at' => now(),
            ]
        );
    }

    private function createFallbackAnalysis(Lead $lead, array $historyContext): AiAnalysis
    {
        $patterns = $historyContext['patterns'] ?? [];
        $summary = $historyContext['summary'] ?? [];

        return AiAnalysis::updateOrCreate(
            ['lead_id' => $lead->id, 'analysis_type' => 'history'],
            [
                'insights' => [
                    'history_summary' => [
                        'total_engagements' => $summary['total'] ?? 0,
                        'time_span_days' => $summary['time_span_days'] ?? 0,
                        'engagement_types_breakdown' => $summary['by_type'] ?? [],
                        'engagement_quality' => $this->determineQuality($summary['total'] ?? 0),
                    ],
                    'communication_patterns' => [
                        'preferred_channel' => $patterns['preferred_channel'] ?? 'email',
                        'engagement_frequency' => $patterns['engagement_frequency'] ?? 'unknown',
                        'most_active_day' => $patterns['most_active_day'] ?? null,
                        'most_active_time' => $patterns['most_active_time'] ?? null,
                    ],
                    'insights' => [
                        'summary' => sprintf(
                            'Lead has %d engagements over %d days. Primary channel: %s.',
                            $summary['total'] ?? 0,
                            $summary['time_span_days'] ?? 0,
                            $patterns['preferred_channel'] ?? 'unknown'
                        ),
                        'engagement_trend' => 'stable',
                        'recommended_approach' => 'Continue regular follow-ups through preferred channel.',
                    ],
                ],
                'recommendations' => $this->generateFallbackRecommendations($lead, $patterns),
                'risks' => [],
                'opportunities' => [],
                'confidence_score' => 50.00,
                'model_used' => 'fallback',
                'analyzed_at' => now(),
            ]
        );
    }

    private function determineQuality(int $total): string
    {
        return match (true) {
            $total >= 20 => 'high',
            $total >= 10 => 'medium',
            $total >= 5 => 'low',
            default => 'minimal',
        };
    }

    private function generateFallbackRecommendations(Lead $lead, array $patterns): array
    {
        $recommendations = [];
        $preferredChannel = $patterns['preferred_channel'] ?? 'email';

        $recommendations[] = [
            'action' => "Schedule a {$preferredChannel} follow-up",
            'priority' => 'medium',
            'optimal_timing' => $patterns['most_active_day'] ?? 'this week',
            'rationale' => 'Based on detected communication patterns',
        ];

        if ($lead->status === 'qualified' || $lead->status === 'proposal') {
            $recommendations[] = [
                'action' => 'Review deal timeline and confirm next steps',
                'priority' => 'high',
                'optimal_timing' => 'immediate',
                'rationale' => 'Lead is in active sales stage',
            ];
        }

        return $recommendations;
    }
}
