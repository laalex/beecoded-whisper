<?php

namespace App\Services\AI;

use App\Models\AiAnalysis;
use App\Models\Lead;

class LeadAnalysisService
{
    public function __construct(
        private AnthropicClient $anthropic,
        private PromptLoader $promptLoader
    ) {}

    /**
     * Get existing analysis or generate new one if none exists.
     * Does NOT auto-regenerate stale analyses - user must explicitly request.
     */
    public function getOrCreateAnalysis(Lead $lead): ?AiAnalysis
    {
        $existingAnalysis = $lead->aiAnalysis;

        if ($existingAnalysis) {
            return $existingAnalysis;
        }

        return $this->analyzeLead($lead);
    }

    /**
     * Force a new analysis (for explicit user requests)
     */
    public function analyzeLead(Lead $lead): ?AiAnalysis
    {
        $context = $this->buildContext($lead);
        $prompt = $this->promptLoader->load('lead_analysis', ['context' => $context]);
        $maxTokens = $this->promptLoader->getMaxTokens('lead_analysis');

        $response = $this->anthropic->sendMessage($prompt, $maxTokens);
        $analysis = $this->anthropic->extractJsonFromResponse($response);

        if (!$analysis) {
            return $this->createFallbackAnalysis($lead);
        }

        return AiAnalysis::updateOrCreate(
            ['lead_id' => $lead->id, 'analysis_type' => 'full'],
            [
                'insights' => $analysis['insights'] ?? $this->generateDefaultInsights($lead),
                'recommendations' => $analysis['recommendations'] ?? [],
                'risks' => $analysis['risks'] ?? [],
                'opportunities' => $analysis['opportunities'] ?? [],
                'confidence_score' => $analysis['confidence_score'] ?? 75.00,
                'model_used' => $this->anthropic->getModel(),
                'analyzed_at' => now(),
            ]
        );
    }

    private function buildContext(Lead $lead): array
    {
        $interactions = $lead->interactions()
            ->orderBy('occurred_at', 'desc')
            ->limit(15)
            ->get();

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
                'days_since_contact' => $lead->last_contacted_at
                    ? $lead->last_contacted_at->diffInDays(now())
                    : null,
                'days_since_created' => $lead->created_at->diffInDays(now()),
            ],
            'interactions' => $interactions->map(fn($i) => [
                'type' => $i->type,
                'direction' => $i->direction,
                'subject' => $i->subject,
                'summary' => $i->summary ?? substr($i->content ?? '', 0, 200),
                'sentiment' => $i->sentiment,
                'date' => $i->occurred_at?->format('Y-m-d'),
            ])->toArray(),
            'enrichment' => $enrichment ? [
                'industry' => $enrichment->industry,
                'employee_count' => $enrichment->employee_count,
                'annual_revenue' => $enrichment->annual_revenue,
                'technologies' => $enrichment->technologies,
                'hubspot_lifecycle' => $enrichment->hubspot_lifecycle_stage,
            ] : null,
            'offers' => $lead->offers()->latest()->limit(3)->get()->map(fn($o) => [
                'title' => $o->title,
                'amount' => $o->amount,
                'status' => $o->status,
            ])->toArray(),
        ];
    }

    private function createFallbackAnalysis(Lead $lead): AiAnalysis
    {
        return AiAnalysis::updateOrCreate(
            ['lead_id' => $lead->id, 'analysis_type' => 'full'],
            [
                'insights' => $this->generateDefaultInsights($lead),
                'recommendations' => $this->generateDefaultRecommendations($lead),
                'risks' => $this->generateDefaultRisks($lead),
                'opportunities' => [],
                'confidence_score' => 50.00,
                'model_used' => 'fallback',
                'analyzed_at' => now(),
            ]
        );
    }

    private function generateDefaultInsights(Lead $lead): array
    {
        $interactionCount = $lead->interactions()->count();
        $daysSinceContact = $lead->last_contacted_at
            ? $lead->last_contacted_at->diffInDays(now())
            : null;

        return [
            'summary' => "Lead in {$lead->status} status with {$interactionCount} recorded interactions.",
            'engagement_level' => $interactionCount >= 5 ? 'high' : ($interactionCount >= 2 ? 'medium' : 'low'),
            'engagement_trend' => 'stable',
            'relationship_health' => $daysSinceContact && $daysSinceContact <= 7 ? 'good' : 'fair',
            'deal_stage_fit' => true,
            'key_interests' => [],
            'communication_preference' => 'email',
            'best_contact_time' => 'morning',
        ];
    }

    private function generateDefaultRecommendations(Lead $lead): array
    {
        $recommendations = [];
        $daysSinceContact = $lead->last_contacted_at
            ? $lead->last_contacted_at->diffInDays(now())
            : 999;

        if ($daysSinceContact > 7) {
            $recommendations[] = [
                'action' => 'Schedule a check-in call or send follow-up email',
                'type' => 'email',
                'priority' => 'high',
                'timing' => 'immediate',
                'rationale' => 'No contact in ' . $daysSinceContact . ' days',
            ];
        }

        if ($lead->status === 'qualified' && !$lead->offers()->exists()) {
            $recommendations[] = [
                'action' => 'Prepare and send a proposal',
                'type' => 'task',
                'priority' => 'high',
                'timing' => 'this_week',
                'rationale' => 'Qualified lead without proposal',
            ];
        }

        return $recommendations;
    }

    private function generateDefaultRisks(Lead $lead): array
    {
        $risks = [];
        $daysSinceContact = $lead->last_contacted_at
            ? $lead->last_contacted_at->diffInDays(now())
            : null;

        if ($daysSinceContact && $daysSinceContact > 14) {
            $risks[] = [
                'factor' => 'engagement_gap',
                'severity' => 'high',
                'description' => 'No contact in ' . $daysSinceContact . ' days',
                'mitigation' => 'Reach out immediately with value-add content',
            ];
        }

        if ($lead->status === 'dormant') {
            $risks[] = [
                'factor' => 'dormant_lead',
                'severity' => 'high',
                'description' => 'Lead marked as dormant',
                'mitigation' => 'Consider reactivation campaign or removal',
            ];
        }

        return $risks;
    }
}
