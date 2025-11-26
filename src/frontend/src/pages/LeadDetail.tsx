import { useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useLead } from '@/hooks/useLead';
import {
  LeadHeader,
  LeadContactInfo,
  LeadScoreCard,
  LeadTimeline,
  LeadReminders,
  LeadOffers,
  LeadNotes,
  AddInteractionModal,
  LeadHubSpotData,
  LeadAIAnalysis,
  LeadHistoryAnalysis,
} from '@/components/leads';
import { Skeleton } from '@/components/common/Skeleton';
import { Card, CardContent } from '@/components/common/Card';
import type { InteractionType, LeadStatus } from '@/types';
import { AlertCircle } from 'lucide-react';

export function LeadDetail() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const {
    lead,
    historyAnalysis,
    hubspotHistory,
    isLoading,
    isError,
    updateLead,
    updateStatus,
    addInteraction,
    syncHubSpot,
    analyzeLead,
    analyzeHistory,
    isUpdating,
    isAddingInteraction,
    isSyncing,
    isAnalyzing,
    isAnalyzingHistory,
  } = useLead(id!);

  const [interactionModal, setInteractionModal] = useState<{
    isOpen: boolean;
    type: InteractionType;
  }>({ isOpen: false, type: 'note' });

  if (isLoading) {
    return (
      <div className="space-y-6">
        {/* Header skeleton */}
        <Card>
          <CardContent className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <Skeleton variant="circular" className="w-14 h-14" />
              <div className="space-y-2">
                <Skeleton className="h-6 w-40" />
                <Skeleton className="h-4 w-32" />
              </div>
            </div>
            <div className="flex items-center gap-3">
              <Skeleton className="h-8 w-24" />
              <Skeleton className="h-8 w-20" />
            </div>
          </CardContent>
        </Card>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2 space-y-6">
            {/* Contact info skeleton */}
            <Card>
              <CardContent className="space-y-4">
                <Skeleton className="h-5 w-32 mb-4" />
                <div className="grid grid-cols-2 gap-4">
                  {Array.from({ length: 6 }).map((_, i) => (
                    <div key={i} className="space-y-1">
                      <Skeleton className="h-3 w-16" />
                      <Skeleton className="h-4 w-32" />
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
            {/* Timeline skeleton */}
            <Card>
              <CardContent>
                <Skeleton className="h-5 w-24 mb-4" />
                <div className="space-y-3">
                  {Array.from({ length: 3 }).map((_, i) => (
                    <div key={i} className="flex gap-3 p-3 bg-surface rounded-lg">
                      <Skeleton variant="circular" className="w-8 h-8 flex-shrink-0" />
                      <div className="flex-1 space-y-2">
                        <Skeleton className="h-4 w-3/4" />
                        <Skeleton className="h-3 w-1/2" />
                      </div>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </div>

          <div className="space-y-6">
            {/* Score card skeleton */}
            <Card>
              <CardContent className="text-center py-6">
                <Skeleton className="h-5 w-20 mx-auto mb-4" />
                <Skeleton variant="circular" className="w-24 h-24 mx-auto mb-4" />
                <div className="space-y-2">
                  <Skeleton className="h-3 w-full" />
                  <Skeleton className="h-3 w-full" />
                  <Skeleton className="h-3 w-full" />
                </div>
              </CardContent>
            </Card>
            {/* Reminders skeleton */}
            <Card>
              <CardContent>
                <Skeleton className="h-5 w-24 mb-4" />
                <div className="space-y-2">
                  {Array.from({ length: 2 }).map((_, i) => (
                    <div key={i} className="p-3 bg-surface rounded-lg">
                      <Skeleton className="h-4 w-3/4 mb-1" />
                      <Skeleton className="h-3 w-1/2" />
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    );
  }

  if (isError || !lead) {
    return (
      <div className="flex flex-col items-center justify-center py-16">
        <AlertCircle className="w-12 h-12 text-red-500 mb-4" />
        <h2 className="text-xl font-semibold text-text mb-2">Lead Not Found</h2>
        <p className="text-text-secondary mb-4">
          The lead you're looking for doesn't exist or you don't have access to it.
        </p>
        <button
          onClick={() => navigate('/leads')}
          className="text-primary hover:underline cursor-pointer"
        >
          Back to Leads
        </button>
      </div>
    );
  }

  const handleStatusChange = (status: LeadStatus) => {
    updateStatus(status);
  };

  const handleQuickAction = (action: 'call' | 'email' | 'note' | 'reminder') => {
    if (action === 'reminder') {
      navigate(`/reminders?lead=${lead.id}`);
      return;
    }
    setInteractionModal({ isOpen: true, type: action });
  };

  const handleAddInteraction = (data: {
    type: InteractionType;
    content: string;
    subject?: string;
    direction?: 'inbound' | 'outbound';
  }) => {
    addInteraction(data, {
      onSuccess: () => {
        setInteractionModal({ isOpen: false, type: 'note' });
      },
    });
  };

  const handleSaveNotes = (notes: string) => {
    updateLead({ notes });
  };

  const handleVipToggle = () => {
    updateLead({ is_vip: !lead.is_vip });
  };

  const isHubSpotLead = lead.source === 'hubspot' && lead.external_id;

  return (
    <div className="space-y-6">
      <LeadHeader
        lead={lead}
        onStatusChange={handleStatusChange}
        onVipToggle={handleVipToggle}
        onQuickAction={handleQuickAction}
        isUpdating={isUpdating}
      />

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 space-y-6">
          <LeadContactInfo lead={lead} />
          <LeadAIAnalysis
            analysis={lead.ai_analysis}
            onReanalyze={analyzeLead}
            isAnalyzing={isAnalyzing}
          />
          {isHubSpotLead && (
            <LeadHistoryAnalysis
              analysis={historyAnalysis}
              onAnalyze={analyzeHistory}
              isAnalyzing={isAnalyzingHistory}
              hasHubSpotData={!!lead.enrichment_data?.hubspot_activities?.length}
              engagements={hubspotHistory?.engagements}
            />
          )}
          <LeadTimeline interactions={lead.interactions || []} />
          <LeadNotes
            notes={lead.notes}
            onSave={handleSaveNotes}
            isSaving={isUpdating}
          />
        </div>

        <div className="space-y-6">
          <LeadScoreCard lead={lead} />
          {isHubSpotLead && (
            <LeadHubSpotData
              enrichmentData={lead.enrichment_data}
              onSync={syncHubSpot}
              isSyncing={isSyncing}
            />
          )}
          <LeadReminders reminders={lead.reminders || []} />
          <LeadOffers offers={lead.offers || []} />
        </div>
      </div>

      <AddInteractionModal
        isOpen={interactionModal.isOpen}
        onClose={() => setInteractionModal({ isOpen: false, type: 'note' })}
        onSubmit={handleAddInteraction}
        initialType={interactionModal.type}
        isSubmitting={isAddingInteraction}
      />
    </div>
  );
}
