<?php

namespace App\Services\Scoring;

use App\Models\Lead;
use App\Models\LeadScore;

class LeadScoringService
{
    private array $weights = [
        'engagement' => 0.30,
        'fit' => 0.25,
        'behavior' => 0.25,
        'recency' => 0.20,
    ];

    public function calculateScore(Lead $lead): LeadScore
    {
        $engagement = $this->calculateEngagementScore($lead);
        $fit = $this->calculateFitScore($lead);
        $behavior = $this->calculateBehaviorScore($lead);
        $recency = $this->calculateRecencyScore($lead);

        $totalScore = (int) (
            ($engagement * $this->weights['engagement']) +
            ($fit * $this->weights['fit']) +
            ($behavior * $this->weights['behavior']) +
            ($recency * $this->weights['recency'])
        );

        $conversionProbability = $this->calculateConversionProbability($totalScore, $lead);

        $scoreDetails = LeadScore::updateOrCreate(
            ['lead_id' => $lead->id],
            [
                'total_score' => $totalScore,
                'engagement_score' => $engagement,
                'fit_score' => $fit,
                'behavior_score' => $behavior,
                'recency_score' => $recency,
                'score_breakdown' => [
                    'engagement' => ['score' => $engagement, 'weight' => $this->weights['engagement']],
                    'fit' => ['score' => $fit, 'weight' => $this->weights['fit']],
                    'behavior' => ['score' => $behavior, 'weight' => $this->weights['behavior']],
                    'recency' => ['score' => $recency, 'weight' => $this->weights['recency']],
                ],
                'factors' => $this->getFactors($lead),
                'conversion_probability' => $conversionProbability,
                'calculated_at' => now(),
            ]
        );

        $lead->update(['score' => $totalScore]);

        return $scoreDetails;
    }

    private function calculateEngagementScore(Lead $lead): int
    {
        $score = 0;
        $interactions = $lead->interactions()->count();
        $emailInteractions = $lead->interactions()->where('type', 'email')->count();
        $callInteractions = $lead->interactions()->where('type', 'call')->count();
        $meetingInteractions = $lead->interactions()->where('type', 'meeting')->count();

        $score += min($interactions * 5, 30);
        $score += min($emailInteractions * 3, 20);
        $score += min($callInteractions * 10, 25);
        $score += min($meetingInteractions * 15, 25);

        return min($score, 100);
    }

    private function calculateFitScore(Lead $lead): int
    {
        $score = 0;

        if ($lead->email) $score += 15;
        if ($lead->phone) $score += 10;
        if ($lead->company) $score += 20;
        if ($lead->job_title) $score += 15;
        if ($lead->website) $score += 10;
        if ($lead->linkedin_url) $score += 10;
        if ($lead->estimated_value && $lead->estimated_value > 0) $score += 20;

        return min($score, 100);
    }

    private function calculateBehaviorScore(Lead $lead): int
    {
        $score = 50;

        $positiveInteractions = $lead->interactions()
            ->where('sentiment', 'positive')
            ->count();

        $negativeInteractions = $lead->interactions()
            ->where('sentiment', 'negative')
            ->count();

        $score += $positiveInteractions * 10;
        $score -= $negativeInteractions * 15;

        if (in_array($lead->status, ['qualified', 'proposal', 'negotiation'])) {
            $score += 25;
        }

        if ($lead->status === 'won') {
            $score = 100;
        }

        if ($lead->status === 'lost') {
            $score = max($score - 30, 0);
        }

        return max(0, min($score, 100));
    }

    private function calculateRecencyScore(Lead $lead): int
    {
        $lastContact = $lead->last_contacted_at ?? $lead->created_at;

        if (!$lastContact) {
            return 20;
        }

        $daysSinceContact = $lastContact->diffInDays(now());

        if ($daysSinceContact <= 1) return 100;
        if ($daysSinceContact <= 3) return 85;
        if ($daysSinceContact <= 7) return 70;
        if ($daysSinceContact <= 14) return 55;
        if ($daysSinceContact <= 30) return 40;
        if ($daysSinceContact <= 60) return 25;

        return 10;
    }

    private function calculateConversionProbability(int $totalScore, Lead $lead): float
    {
        $baseProbability = $totalScore / 100 * 0.5;

        if (in_array($lead->status, ['proposal', 'negotiation'])) {
            $baseProbability *= 1.5;
        }

        if ($lead->status === 'qualified') {
            $baseProbability *= 1.2;
        }

        return min(round($baseProbability * 100, 2), 95.00);
    }

    private function getFactors(Lead $lead): array
    {
        $factors = [];

        if (!$lead->email) {
            $factors[] = ['type' => 'negative', 'message' => 'No email address'];
        }

        if (!$lead->phone) {
            $factors[] = ['type' => 'negative', 'message' => 'No phone number'];
        }

        if (!$lead->company) {
            $factors[] = ['type' => 'negative', 'message' => 'No company information'];
        }

        $lastContact = $lead->last_contacted_at;
        if ($lastContact && $lastContact->diffInDays(now()) > 14) {
            $factors[] = ['type' => 'warning', 'message' => 'No recent contact'];
        }

        if ($lead->interactions()->count() >= 3) {
            $factors[] = ['type' => 'positive', 'message' => 'High engagement'];
        }

        if ($lead->estimated_value && $lead->estimated_value > 10000) {
            $factors[] = ['type' => 'positive', 'message' => 'High value opportunity'];
        }

        return $factors;
    }

    public function recalculateAllScores(): int
    {
        $count = 0;
        Lead::chunk(100, function ($leads) use (&$count) {
            foreach ($leads as $lead) {
                $this->calculateScore($lead);
                $count++;
            }
        });

        return $count;
    }
}
