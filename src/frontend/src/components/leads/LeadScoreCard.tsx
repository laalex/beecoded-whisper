import { Card, CardContent, CardHeader, CardTitle } from '@/components/common/Card';
import type { Lead } from '@/types';
import { TrendingUp, TrendingDown, AlertCircle } from 'lucide-react';

interface LeadScoreCardProps {
  lead: Lead;
}

export function LeadScoreCard({ lead }: LeadScoreCardProps) {
  const scoreDetails = lead.score_details;
  const score = scoreDetails?.total_score ?? lead.score;

  const getScoreColor = (value: number) => {
    if (value >= 70) return 'text-green-500';
    if (value >= 40) return 'text-yellow-500';
    return 'text-red-500';
  };

  const getScoreBarColor = (value: number) => {
    if (value >= 70) return 'bg-green-500';
    if (value >= 40) return 'bg-yellow-500';
    return 'bg-red-500';
  };

  const ScoreBar = ({ label, value }: { label: string; value: number }) => (
    <div className="space-y-1">
      <div className="flex justify-between text-sm">
        <span className="text-text-secondary">{label}</span>
        <span className="text-text font-medium">{value}</span>
      </div>
      <div className="h-2 bg-surface rounded-full overflow-hidden">
        <div
          className={`h-full rounded-full transition-all ${getScoreBarColor(value)}`}
          style={{ width: `${value}%` }}
        />
      </div>
    </div>
  );

  return (
    <Card>
      <CardHeader>
        <CardTitle>Lead Score</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="text-center mb-6">
          <div className={`text-5xl font-bold ${getScoreColor(score)}`}>
            {score}
          </div>
          <p className="text-text-secondary text-sm mt-1">out of 100</p>
        </div>

        {scoreDetails && (
          <>
            <div className="space-y-4 mb-6">
              <ScoreBar label="Engagement" value={scoreDetails.engagement_score} />
              <ScoreBar label="Fit" value={scoreDetails.fit_score} />
              <ScoreBar label="Behavior" value={scoreDetails.behavior_score} />
              <ScoreBar label="Recency" value={scoreDetails.recency_score} />
            </div>

            {scoreDetails.conversion_probability > 0 && (
              <div className="bg-surface rounded-lg p-3 mb-4">
                <p className="text-sm text-text-secondary">Conversion Probability</p>
                <p className="text-lg font-semibold text-text">
                  {Math.round(scoreDetails.conversion_probability * 100)}%
                </p>
              </div>
            )}

            {scoreDetails.factors && scoreDetails.factors.length > 0 && (
              <div className="space-y-2">
                <p className="text-sm font-medium text-text">Score Factors</p>
                {scoreDetails.factors.map((factor, index) => (
                  <div
                    key={index}
                    className="flex items-start gap-2 text-sm"
                  >
                    {factor.type === 'positive' && (
                      <TrendingUp className="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" />
                    )}
                    {factor.type === 'negative' && (
                      <TrendingDown className="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" />
                    )}
                    {factor.type === 'warning' && (
                      <AlertCircle className="w-4 h-4 text-yellow-500 mt-0.5 flex-shrink-0" />
                    )}
                    <span className="text-text-secondary">{factor.message}</span>
                  </div>
                ))}
              </div>
            )}
          </>
        )}
      </CardContent>
    </Card>
  );
}
