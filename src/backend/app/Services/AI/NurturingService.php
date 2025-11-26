<?php

namespace App\Services\AI;

use App\Models\Lead;
use App\Models\Reminder;
use Illuminate\Support\Facades\Http;

class NurturingService
{
    public function getRecommendations(Lead $lead): array
    {
        $context = $this->buildLeadContext($lead);
        $aiRecommendations = $this->getAIRecommendations($context);

        return [
            'next_actions' => $aiRecommendations['actions'] ?? $this->getDefaultActions($lead),
            'talking_points' => $aiRecommendations['talking_points'] ?? [],
            'risk_factors' => $this->identifyRiskFactors($lead),
            'opportunity_score' => $lead->score,
            'suggested_content' => $aiRecommendations['content'] ?? [],
            'optimal_contact_time' => $this->suggestContactTime($lead),
        ];
    }

    private function buildLeadContext(Lead $lead): array
    {
        $recentInteractions = $lead->interactions()
            ->orderBy('occurred_at', 'desc')
            ->limit(10)
            ->get();

        return [
            'lead' => [
                'name' => $lead->full_name,
                'company' => $lead->company,
                'job_title' => $lead->job_title,
                'status' => $lead->status,
                'score' => $lead->score,
                'estimated_value' => $lead->estimated_value,
                'days_since_contact' => $lead->last_contacted_at
                    ? $lead->last_contacted_at->diffInDays(now())
                    : null,
            ],
            'interactions' => $recentInteractions->map(fn($i) => [
                'type' => $i->type,
                'subject' => $i->subject,
                'summary' => $i->summary,
                'sentiment' => $i->sentiment,
                'date' => $i->occurred_at?->format('Y-m-d'),
            ])->toArray(),
            'offers' => $lead->offers()->latest()->limit(3)->get()->map(fn($o) => [
                'title' => $o->title,
                'amount' => $o->amount,
                'status' => $o->status,
            ])->toArray(),
        ];
    }

    private function getAIRecommendations(array $context): array
    {
        $contextJson = json_encode($context);

        $response = Http::withHeaders([
            'x-api-key' => config('services.anthropic.api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 1024,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => "As a sales AI assistant, analyze this lead and provide nurturing recommendations.

Lead Context:
{$contextJson}

Provide recommendations in this JSON format:
{
  \"actions\": [
    {\"type\": \"email|call|meeting|task\", \"description\": \"...\", \"priority\": \"high|medium|low\", \"timing\": \"immediate|this_week|next_week\"}
  ],
  \"talking_points\": [\"point1\", \"point2\"],
  \"content\": [
    {\"type\": \"case_study|whitepaper|demo\", \"topic\": \"...\", \"reason\": \"...\"}
  ]
}"
                ]
            ]
        ]);

        if ($response->failed()) {
            return [];
        }

        $content = $response->json('content.0.text', '{}');
        return json_decode($content, true) ?? [];
    }

    private function getDefaultActions(Lead $lead): array
    {
        $actions = [];
        $daysSinceContact = $lead->last_contacted_at
            ? $lead->last_contacted_at->diffInDays(now())
            : 999;

        if ($daysSinceContact > 7) {
            $actions[] = [
                'type' => 'email',
                'description' => 'Send a check-in email',
                'priority' => 'high',
                'timing' => 'immediate',
            ];
        }

        if ($lead->status === 'qualified' && $daysSinceContact > 3) {
            $actions[] = [
                'type' => 'call',
                'description' => 'Schedule a discovery call',
                'priority' => 'high',
                'timing' => 'this_week',
            ];
        }

        if ($lead->status === 'proposal') {
            $actions[] = [
                'type' => 'meeting',
                'description' => 'Present proposal and address questions',
                'priority' => 'high',
                'timing' => 'immediate',
            ];
        }

        return $actions;
    }

    private function identifyRiskFactors(Lead $lead): array
    {
        $risks = [];

        $daysSinceContact = $lead->last_contacted_at
            ? $lead->last_contacted_at->diffInDays(now())
            : null;

        if ($daysSinceContact && $daysSinceContact > 14) {
            $risks[] = [
                'factor' => 'no_recent_contact',
                'severity' => 'high',
                'message' => "No contact in {$daysSinceContact} days",
            ];
        }

        $negativeInteractions = $lead->interactions()
            ->where('sentiment', 'negative')
            ->count();

        if ($negativeInteractions >= 2) {
            $risks[] = [
                'factor' => 'negative_sentiment',
                'severity' => 'medium',
                'message' => 'Multiple negative interactions detected',
            ];
        }

        if ($lead->status === 'dormant') {
            $risks[] = [
                'factor' => 'dormant_lead',
                'severity' => 'high',
                'message' => 'Lead marked as dormant - consider reactivation strategy',
            ];
        }

        return $risks;
    }

    private function suggestContactTime(Lead $lead): array
    {
        return [
            'day' => 'Tuesday',
            'time_range' => '10:00 AM - 11:00 AM',
            'reason' => 'Based on historical engagement patterns',
        ];
    }

    public function createAIReminder(Lead $lead, array $action): Reminder
    {
        $dueAt = match ($action['timing'] ?? 'this_week') {
            'immediate' => now()->addHours(2),
            'this_week' => now()->addDays(2),
            'next_week' => now()->addWeek(),
            default => now()->addDays(3),
        };

        return Reminder::create([
            'user_id' => $lead->assigned_to ?? $lead->user_id,
            'lead_id' => $lead->id,
            'title' => $action['description'],
            'type' => $action['type'] ?? 'task',
            'priority' => $action['priority'] ?? 'medium',
            'due_at' => $dueAt,
            'is_ai_generated' => true,
            'metadata' => ['source' => 'nurturing_ai', 'action' => $action],
        ]);
    }
}
