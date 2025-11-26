import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/common/Card';
import { Badge } from '@/components/common/Badge';
import Button from '@/components/common/Button';
import { EngagementDetailsModal } from './EngagementDetailsModal';
import type { AiAnalysis, HistoryAnalysisInsights, HubSpotEngagement } from '@/types';
import {
  History,
  RefreshCw,
  TrendingUp,
  TrendingDown,
  AlertTriangle,
  Target,
  MessageSquare,
  Calendar,
  Clock,
  CheckCircle,
  XCircle,
  AlertCircle,
} from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';

interface LeadHistoryAnalysisProps {
  analysis: AiAnalysis | null | undefined;
  onAnalyze?: () => void;
  isAnalyzing?: boolean;
  hasHubSpotData?: boolean;
  engagements?: HubSpotEngagement[];
}

export function LeadHistoryAnalysis({
  analysis,
  onAnalyze,
  isAnalyzing,
  hasHubSpotData,
  engagements = [],
}: LeadHistoryAnalysisProps) {
  const [selectedType, setSelectedType] = useState<string | null>(null);

  const filteredEngagements = selectedType
    ? engagements.filter((e) => e.type.toLowerCase() === selectedType.toLowerCase())
    : [];
  if (!analysis || analysis.analysis_type !== 'history') {
    return (
      <Card>
        <CardHeader className="flex flex-row items-center justify-between">
          <CardTitle className="flex items-center gap-2">
            <History className="w-5 h-5 text-primary" />
            History Analysis
          </CardTitle>
          {onAnalyze && hasHubSpotData && (
            <Button size="sm" onClick={onAnalyze} disabled={isAnalyzing}>
              {isAnalyzing ? 'Analyzing...' : 'Analyze History'}
            </Button>
          )}
        </CardHeader>
        <CardContent>
          <p className="text-text-secondary text-center py-4">
            {hasHubSpotData
              ? 'Analyze complete HubSpot history for deep insights.'
              : 'Sync HubSpot data first to enable history analysis.'}
          </p>
        </CardContent>
      </Card>
    );
  }

  const insights = analysis.insights as unknown as HistoryAnalysisInsights;
  const histSummary = insights?.history_summary;
  const patterns = insights?.communication_patterns;
  const timeline = insights?.relationship_timeline;
  const buyingSignals = insights?.buying_signals;
  const objections = insights?.objections_raised;
  const prediction = insights?.deal_prediction;
  const generalInsights = insights?.insights;

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between">
        <div className="flex items-center gap-2">
          <CardTitle className="flex items-center gap-2">
            <History className="w-5 h-5 text-primary" />
            History Analysis
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
          {onAnalyze && (
            <Button variant="ghost" size="sm" onClick={onAnalyze} disabled={isAnalyzing}>
              <RefreshCw className={`w-4 h-4 ${isAnalyzing ? 'animate-spin' : ''}`} />
            </Button>
          )}
        </div>
      </CardHeader>
      <CardContent className="space-y-4">
        {/* Summary */}
        {generalInsights?.summary && (
          <p className="text-text">{generalInsights.summary}</p>
        )}

        {/* History Stats */}
        {histSummary && (
          <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div className="bg-surface rounded-lg p-3 text-center">
              <p className="text-2xl font-bold text-primary">{histSummary.total_engagements}</p>
              <p className="text-xs text-text-secondary">Total Engagements</p>
            </div>
            {histSummary.time_span_days !== undefined && (
              <div className="bg-surface rounded-lg p-3 text-center">
                <p className="text-2xl font-bold text-text">{histSummary.time_span_days}</p>
                <p className="text-xs text-text-secondary">Days of History</p>
              </div>
            )}
            {histSummary.engagement_quality && (
              <div className="bg-surface rounded-lg p-3 text-center">
                <Badge
                  variant={
                    histSummary.engagement_quality === 'high'
                      ? 'success'
                      : histSummary.engagement_quality === 'medium'
                      ? 'warning'
                      : 'default'
                  }
                  className="text-sm"
                >
                  {histSummary.engagement_quality}
                </Badge>
                <p className="text-xs text-text-secondary mt-1">Quality</p>
              </div>
            )}
            {prediction?.likelihood_to_close !== undefined && (
              <div className="bg-surface rounded-lg p-3 text-center">
                <p className="text-2xl font-bold text-green-500">{prediction.likelihood_to_close}%</p>
                <p className="text-xs text-text-secondary">Close Likelihood</p>
              </div>
            )}
          </div>
        )}

        {/* Engagement Breakdown */}
        {histSummary?.engagement_types_breakdown && (
          <div className="border-t border-border pt-4">
            <p className="text-sm font-medium text-text mb-2 flex items-center gap-2">
              <MessageSquare className="w-4 h-4" />
              Engagement Breakdown
            </p>
            <div className="flex flex-wrap gap-2">
              {Object.entries(histSummary.engagement_types_breakdown).map(([type, count]) => (
                <button
                  key={type}
                  onClick={() => setSelectedType(type)}
                  className="cursor-pointer hover:opacity-80 transition-opacity"
                >
                  <Badge variant="default" className="capitalize">
                    {type}: {count}
                  </Badge>
                </button>
              ))}
            </div>
          </div>
        )}

        {/* Communication Patterns */}
        {patterns && (
          <div className="border-t border-border pt-4">
            <p className="text-sm font-medium text-text mb-3 flex items-center gap-2">
              <Clock className="w-4 h-4" />
              Communication Patterns
            </p>
            <div className="grid grid-cols-2 gap-3">
              {patterns.preferred_channel && (
                <div className="bg-surface rounded-lg p-3">
                  <p className="text-xs text-text-secondary">Preferred Channel</p>
                  <p className="text-sm text-text capitalize font-medium">{patterns.preferred_channel}</p>
                </div>
              )}
              {patterns.most_active_day && (
                <div className="bg-surface rounded-lg p-3">
                  <p className="text-xs text-text-secondary">Most Active Day</p>
                  <p className="text-sm text-text font-medium">{patterns.most_active_day}</p>
                </div>
              )}
              {patterns.most_active_time && (
                <div className="bg-surface rounded-lg p-3">
                  <p className="text-xs text-text-secondary">Best Time</p>
                  <p className="text-sm text-text capitalize font-medium">{patterns.most_active_time}</p>
                </div>
              )}
              {patterns.engagement_frequency && (
                <div className="bg-surface rounded-lg p-3">
                  <p className="text-xs text-text-secondary">Frequency</p>
                  <p className="text-sm text-text font-medium">{patterns.engagement_frequency}</p>
                </div>
              )}
            </div>
            {patterns.response_pattern && (
              <p className="text-sm text-text-secondary mt-2 italic">"{patterns.response_pattern}"</p>
            )}
          </div>
        )}

        {/* Relationship Timeline */}
        {timeline && timeline.phases && timeline.phases.length > 0 && (
          <div className="border-t border-border pt-4">
            <p className="text-sm font-medium text-text mb-3 flex items-center gap-2">
              <Calendar className="w-4 h-4" />
              Relationship Journey
              {timeline.relationship_health && (
                <Badge
                  variant={
                    timeline.relationship_health === 'strong' || timeline.relationship_health === 'good'
                      ? 'success'
                      : timeline.relationship_health === 'fair'
                      ? 'warning'
                      : 'danger'
                  }
                >
                  {timeline.relationship_health}
                </Badge>
              )}
            </p>
            <div className="space-y-2">
              {timeline.phases.slice(0, 4).map((phase, idx) => (
                <div key={idx} className="flex items-start gap-3 bg-surface rounded-lg p-3">
                  <div className="flex-shrink-0 w-2 h-2 mt-2 rounded-full bg-primary" />
                  <div className="flex-1">
                    <div className="flex items-center justify-between">
                      <span className="text-sm font-medium text-text capitalize">
                        {phase.phase.replace(/_/g, ' ')}
                      </span>
                      <span className="text-xs text-text-secondary">{phase.period}</span>
                    </div>
                    {phase.key_events && phase.key_events.length > 0 && (
                      <p className="text-xs text-text-secondary mt-1">
                        {phase.key_events.join(' • ')}
                      </p>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Buying Signals */}
        {buyingSignals && buyingSignals.length > 0 && (
          <div className="border-t border-border pt-4">
            <p className="text-sm font-medium text-text mb-3 flex items-center gap-2">
              <Target className="w-4 h-4 text-green-500" />
              Buying Signals
            </p>
            <div className="space-y-2">
              {buyingSignals.map((signal, idx) => (
                <div key={idx} className="bg-green-50 border border-green-200 rounded-lg p-3">
                  <div className="flex items-center justify-between">
                    <p className="text-sm text-green-800">{signal.signal}</p>
                    <Badge
                      variant={signal.strength === 'strong' ? 'success' : signal.strength === 'moderate' ? 'warning' : 'default'}
                      className="text-xs"
                    >
                      {signal.strength}
                    </Badge>
                  </div>
                  {signal.context && (
                    <p className="text-xs text-green-600 mt-1">{signal.context}</p>
                  )}
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Objections */}
        {objections && objections.length > 0 && (
          <div className="border-t border-border pt-4">
            <p className="text-sm font-medium text-text mb-3 flex items-center gap-2">
              <AlertTriangle className="w-4 h-4 text-amber-500" />
              Objections Raised
            </p>
            <div className="space-y-2">
              {objections.map((obj, idx) => (
                <div
                  key={idx}
                  className={`rounded-lg p-3 border ${
                    obj.status === 'addressed'
                      ? 'bg-green-50 border-green-200'
                      : obj.status === 'partially_addressed'
                      ? 'bg-amber-50 border-amber-200'
                      : 'bg-red-50 border-red-200'
                  }`}
                >
                  <div className="flex items-center gap-2">
                    {obj.status === 'addressed' && <CheckCircle className="w-4 h-4 text-green-500" />}
                    {obj.status === 'partially_addressed' && <AlertCircle className="w-4 h-4 text-amber-500" />}
                    {obj.status === 'unresolved' && <XCircle className="w-4 h-4 text-red-500" />}
                    <p className="text-sm font-medium">{obj.objection}</p>
                  </div>
                  {obj.resolution && (
                    <p className="text-xs text-text-secondary mt-1 ml-6">Resolution: {obj.resolution}</p>
                  )}
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Deal Prediction */}
        {prediction && (
          <div className="border-t border-border pt-4">
            <p className="text-sm font-medium text-text mb-3 flex items-center gap-2">
              <TrendingUp className="w-4 h-4 text-primary" />
              Deal Prediction
            </p>
            <div className="bg-surface rounded-lg p-4">
              <div className="flex items-center justify-between mb-3">
                <div>
                  <p className="text-xs text-text-secondary">Likelihood to Close</p>
                  <p className="text-3xl font-bold text-primary">{prediction.likelihood_to_close}%</p>
                </div>
                {prediction.estimated_close_timeframe && (
                  <div className="text-right">
                    <p className="text-xs text-text-secondary">Estimated Timeframe</p>
                    <p className="text-lg font-medium text-text">{prediction.estimated_close_timeframe}</p>
                  </div>
                )}
              </div>
              {prediction.key_factors && prediction.key_factors.length > 0 && (
                <div className="mb-2">
                  <p className="text-xs text-text-secondary mb-1">Key Factors</p>
                  <div className="flex flex-wrap gap-1">
                    {prediction.key_factors.map((factor, idx) => (
                      <Badge key={idx} variant="success" className="text-xs">
                        {factor}
                      </Badge>
                    ))}
                  </div>
                </div>
              )}
              {prediction.risks_to_close && prediction.risks_to_close.length > 0 && (
                <div>
                  <p className="text-xs text-text-secondary mb-1">Risks</p>
                  <div className="flex flex-wrap gap-1">
                    {prediction.risks_to_close.map((risk, idx) => (
                      <Badge key={idx} variant="danger" className="text-xs">
                        {risk}
                      </Badge>
                    ))}
                  </div>
                </div>
              )}
            </div>
          </div>
        )}

        {/* Recommendations */}
        {analysis.recommendations && analysis.recommendations.length > 0 && (
          <div className="border-t border-border pt-4">
            <p className="text-sm font-medium text-text mb-3">Next Best Actions</p>
            <div className="space-y-2">
              {analysis.recommendations.slice(0, 4).map((rec, idx) => (
                <div key={idx} className="bg-surface rounded-lg p-3">
                  <div className="flex items-start justify-between">
                    <p className="text-sm text-text">{rec.action}</p>
                    <Badge
                      variant={
                        rec.priority === 'high'
                          ? 'danger'
                          : rec.priority === 'medium'
                          ? 'warning'
                          : 'default'
                      }
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

        {/* Engagement Trend */}
        {generalInsights?.engagement_trend && (
          <div className="flex items-center justify-center gap-2 pt-2 border-t border-border">
            <span className="text-sm text-text-secondary">Engagement Trend:</span>
            <div className="flex items-center gap-1">
              {generalInsights.engagement_trend === 'improving' && (
                <>
                  <TrendingUp className="w-4 h-4 text-green-500" />
                  <span className="text-green-500 font-medium">Improving</span>
                </>
              )}
              {generalInsights.engagement_trend === 'stable' && (
                <span className="text-text font-medium">Stable</span>
              )}
              {generalInsights.engagement_trend === 'declining' && (
                <>
                  <TrendingDown className="w-4 h-4 text-red-500" />
                  <span className="text-red-500 font-medium">Declining</span>
                </>
              )}
            </div>
          </div>
        )}
      </CardContent>

      <EngagementDetailsModal
        isOpen={selectedType !== null}
        onClose={() => setSelectedType(null)}
        engagementType={selectedType || ''}
        engagements={filteredEngagements}
      />
    </Card>
  );
}
