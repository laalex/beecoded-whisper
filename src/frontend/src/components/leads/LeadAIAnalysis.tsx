import { Card, CardContent, CardHeader, CardTitle } from '@/components/common/Card';
import { Badge } from '@/components/common/Badge';
import Button from '@/components/common/Button';
import type { AiAnalysis } from '@/types';
import { Brain, RefreshCw, TrendingUp, TrendingDown, AlertTriangle, Lightbulb, Clock } from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';

interface LeadAIAnalysisProps {
  analysis: AiAnalysis | null | undefined;
  onReanalyze?: () => void;
  isAnalyzing?: boolean;
}

export function LeadAIAnalysis({ analysis, onReanalyze, isAnalyzing }: LeadAIAnalysisProps) {
  if (!analysis) {
    return (
      <Card>
        <CardHeader className="flex flex-row items-center justify-between">
          <CardTitle className="flex items-center gap-2">
            <Brain className="w-5 h-5 text-primary" />
            AI Analysis
          </CardTitle>
          {onReanalyze && (
            <Button size="sm" onClick={onReanalyze} disabled={isAnalyzing}>
              {isAnalyzing ? 'Analyzing...' : 'Generate Analysis'}
            </Button>
          )}
        </CardHeader>
        <CardContent>
          <p className="text-text-secondary text-center py-4">
            No analysis available yet. Click to generate AI insights.
          </p>
        </CardContent>
      </Card>
    );
  }

  const insights = analysis.insights;
  const engagementColors: Record<string, 'success' | 'warning' | 'danger'> = {
    high: 'success',
    medium: 'warning',
    low: 'danger',
  };

  const healthColors: Record<string, 'success' | 'warning' | 'danger' | 'default'> = {
    strong: 'success',
    good: 'success',
    fair: 'warning',
    weak: 'danger',
  };

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between">
        <div className="flex items-center gap-2">
          <CardTitle className="flex items-center gap-2">
            <Brain className="w-5 h-5 text-primary" />
            AI Analysis
          </CardTitle>
          {analysis.confidence_score && (
            <Badge variant="info" className="text-xs">
              {analysis.confidence_score}% confidence
            </Badge>
          )}
        </div>
        <div className="flex items-center gap-2">
          {analysis.analyzed_at && (
            <span className="text-xs text-text-secondary">
              {formatDistanceToNow(new Date(analysis.analyzed_at), { addSuffix: true })}
            </span>
          )}
          {onReanalyze && (
            <Button variant="ghost" size="sm" onClick={onReanalyze} disabled={isAnalyzing}>
              <RefreshCw className={`w-4 h-4 ${isAnalyzing ? 'animate-spin' : ''}`} />
            </Button>
          )}
        </div>
      </CardHeader>
      <CardContent className="space-y-4">
        {insights?.summary && (
          <p className="text-text">{insights.summary}</p>
        )}

        <div className="grid grid-cols-2 gap-3">
          {insights?.engagement_level && (
            <div className="bg-surface rounded-lg p-3">
              <p className="text-xs text-text-secondary mb-1">Engagement</p>
              <div className="flex items-center gap-2">
                <Badge variant={engagementColors[insights.engagement_level]}>
                  {insights.engagement_level}
                </Badge>
                {insights.engagement_trend === 'improving' && (
                  <TrendingUp className="w-4 h-4 text-green-500" />
                )}
                {insights.engagement_trend === 'declining' && (
                  <TrendingDown className="w-4 h-4 text-red-500" />
                )}
              </div>
            </div>
          )}

          {insights?.relationship_health && (
            <div className="bg-surface rounded-lg p-3">
              <p className="text-xs text-text-secondary mb-1">Relationship</p>
              <Badge variant={healthColors[insights.relationship_health]}>
                {insights.relationship_health}
              </Badge>
            </div>
          )}

          {insights?.communication_preference && (
            <div className="bg-surface rounded-lg p-3">
              <p className="text-xs text-text-secondary mb-1">Prefers</p>
              <p className="text-sm text-text capitalize">{insights.communication_preference}</p>
            </div>
          )}

          {insights?.best_contact_time && (
            <div className="bg-surface rounded-lg p-3">
              <p className="text-xs text-text-secondary mb-1">Best Time</p>
              <div className="flex items-center gap-1">
                <Clock className="w-3 h-3 text-text-secondary" />
                <p className="text-sm text-text capitalize">{insights.best_contact_time}</p>
              </div>
            </div>
          )}
        </div>

        {insights?.key_interests && insights.key_interests.length > 0 && (
          <div>
            <p className="text-sm font-medium text-text mb-2">Key Interests</p>
            <div className="flex flex-wrap gap-2">
              {insights.key_interests.map((interest, idx) => (
                <Badge key={idx} variant="default">{interest}</Badge>
              ))}
            </div>
          </div>
        )}

        {analysis.recommendations && analysis.recommendations.length > 0 && (
          <div className="border-t border-border pt-4">
            <p className="text-sm font-medium text-text mb-3 flex items-center gap-2">
              <Lightbulb className="w-4 h-4 text-yellow-500" />
              Recommendations
            </p>
            <div className="space-y-2">
              {analysis.recommendations.slice(0, 3).map((rec, idx) => (
                <div key={idx} className="bg-surface rounded-lg p-3">
                  <div className="flex items-start justify-between">
                    <p className="text-sm text-text">{rec.action}</p>
                    <Badge
                      variant={rec.priority === 'high' ? 'danger' : rec.priority === 'medium' ? 'warning' : 'default'}
                      className="text-xs ml-2"
                    >
                      {rec.priority}
                    </Badge>
                  </div>
                  <p className="text-xs text-text-secondary mt-1">{rec.rationale}</p>
                </div>
              ))}
            </div>
          </div>
        )}

        {analysis.risks && analysis.risks.length > 0 && (
          <div className="border-t border-border pt-4">
            <p className="text-sm font-medium text-text mb-3 flex items-center gap-2">
              <AlertTriangle className="w-4 h-4 text-red-500" />
              Risk Factors
            </p>
            <div className="space-y-2">
              {analysis.risks.map((risk, idx) => (
                <div key={idx} className="bg-red-50 border border-red-200 rounded-lg p-3">
                  <div className="flex items-center justify-between">
                    <p className="text-sm font-medium text-red-800">{risk.description}</p>
                    <Badge variant="danger" className="text-xs">{risk.severity}</Badge>
                  </div>
                  <p className="text-xs text-red-600 mt-1">Mitigation: {risk.mitigation}</p>
                </div>
              ))}
            </div>
          </div>
        )}

        {analysis.opportunities && analysis.opportunities.length > 0 && (
          <div className="border-t border-border pt-4">
            <p className="text-sm font-medium text-text mb-3 flex items-center gap-2">
              <TrendingUp className="w-4 h-4 text-green-500" />
              Opportunities
            </p>
            <div className="space-y-2">
              {analysis.opportunities.map((opp, idx) => (
                <div key={idx} className="bg-green-50 border border-green-200 rounded-lg p-3">
                  <p className="text-sm text-green-800">{opp.description}</p>
                  <div className="flex items-center justify-between mt-1">
                    <Badge variant="success" className="text-xs">{opp.type}</Badge>
                    <span className="text-xs text-green-600">{opp.potential_value}</span>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
