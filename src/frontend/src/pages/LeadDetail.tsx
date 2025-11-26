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
} from '@/components/leads';
import type { InteractionType, LeadStatus } from '@/types';
import { AlertCircle } from 'lucide-react';

export function LeadDetail() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const {
    lead,
    isLoading,
    isError,
    updateLead,
    updateStatus,
    addInteraction,
    syncHubSpot,
    analyzeLead,
    isUpdating,
    isAddingInteraction,
    isSyncing,
    isAnalyzing,
  } = useLead(id!);

  const [interactionModal, setInteractionModal] = useState<{
    isOpen: boolean;
    type: InteractionType;
  }>({ isOpen: false, type: 'note' });

  if (isLoading) {
    return (
      <div className="space-y-6">
        <div className="h-8 w-32 bg-surface rounded animate-pulse" />
        <div className="h-24 bg-surface rounded-xl animate-pulse" />
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2 space-y-6">
            <div className="h-48 bg-surface rounded-xl animate-pulse" />
            <div className="h-64 bg-surface rounded-xl animate-pulse" />
          </div>
          <div className="space-y-6">
            <div className="h-48 bg-surface rounded-xl animate-pulse" />
            <div className="h-32 bg-surface rounded-xl animate-pulse" />
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
          className="text-primary hover:underline"
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

  const isHubSpotLead = lead.source === 'hubspot' && lead.external_id;

  return (
    <div className="space-y-6">
      <LeadHeader
        lead={lead}
        onStatusChange={handleStatusChange}
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
