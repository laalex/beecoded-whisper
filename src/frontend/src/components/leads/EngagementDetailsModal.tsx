import { X, Mail, Phone, Calendar, FileText, CheckSquare, Clock } from 'lucide-react';
import { Badge } from '@/components/common/Badge';
import type { HubSpotEngagement, HubSpotEmailParticipant } from '@/types';
import { format } from 'date-fns';

function formatParticipant(participant: string | HubSpotEmailParticipant): string {
  if (typeof participant === 'string') return participant;
  if (participant.firstName && participant.lastName) {
    return `${participant.firstName} ${participant.lastName}${participant.email ? ` <${participant.email}>` : ''}`;
  }
  return participant.email || participant.raw || 'Unknown';
}

interface EngagementDetailsModalProps {
  isOpen: boolean;
  onClose: () => void;
  engagementType: string;
  engagements: HubSpotEngagement[];
}

const typeIcons: Record<string, React.ElementType> = {
  email: Mail,
  call: Phone,
  meeting: Calendar,
  note: FileText,
  task: CheckSquare,
};

function formatTimestamp(engagement: HubSpotEngagement): string {
  const ts = engagement.timestamp || (engagement.created_at ? new Date(engagement.created_at).getTime() : null);
  if (!ts) return 'Unknown date';
  return format(new Date(ts), 'MMM d, yyyy h:mm a');
}

function getEngagementTitle(engagement: HubSpotEngagement): string {
  const { metadata, type } = engagement;
  if (metadata.subject) return metadata.subject;
  if (metadata.title) return metadata.title;
  if (type === 'CALL') return metadata.status ? `Call - ${metadata.status}` : 'Call';
  if (type === 'MEETING') return 'Meeting';
  return `${type.charAt(0).toUpperCase() + type.slice(1).toLowerCase()}`;
}

function getEngagementContent(engagement: HubSpotEngagement): string | null {
  const { metadata } = engagement;
  return metadata.text || metadata.body || null;
}

function getEngagementDetails(engagement: HubSpotEngagement): React.ReactNode {
  const { metadata, type } = engagement;
  const details: React.ReactNode[] = [];

  if (metadata.from) {
    details.push(
      <span key="from" className="text-text-secondary">
        From: <span className="text-text">{formatParticipant(metadata.from)}</span>
      </span>
    );
  }

  if (metadata.to && metadata.to.length > 0) {
    details.push(
      <span key="to" className="text-text-secondary">
        To: <span className="text-text">{metadata.to.map(formatParticipant).join(', ')}</span>
      </span>
    );
  }

  if (type === 'CALL' && metadata.duration_seconds) {
    const mins = Math.floor(metadata.duration_seconds / 60);
    const secs = metadata.duration_seconds % 60;
    details.push(
      <span key="duration" className="text-text-secondary">
        Duration: <span className="text-text">{mins}m {secs}s</span>
      </span>
    );
  }

  if (metadata.status) {
    details.push(
      <span key="status" className="text-text-secondary">
        Status: <span className="text-text capitalize">{metadata.status}</span>
      </span>
    );
  }

  return details.length > 0 ? (
    <div className="flex flex-wrap gap-3 text-sm mb-2">
      {details}
    </div>
  ) : null;
}

export function EngagementDetailsModal({
  isOpen,
  onClose,
  engagementType,
  engagements,
}: EngagementDetailsModalProps) {
  if (!isOpen) return null;

  const Icon = typeIcons[engagementType.toLowerCase()] || FileText;
  const displayType = engagementType.charAt(0).toUpperCase() + engagementType.slice(1).toLowerCase();

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center">
      <div
        className="absolute inset-0 bg-black/50"
        onClick={onClose}
      />
      <div className="relative bg-background rounded-xl shadow-xl w-full max-w-2xl mx-4 max-h-[80vh] flex flex-col">
        <div className="flex items-center justify-between p-6 border-b border-border">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
              <Icon className="w-5 h-5 text-primary" />
            </div>
            <div>
              <h2 className="text-xl font-semibold text-text">{displayType}s</h2>
              <p className="text-sm text-text-secondary">{engagements.length} engagement{engagements.length !== 1 ? 's' : ''}</p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="p-2 hover:bg-surface rounded-lg transition-colors"
          >
            <X className="w-5 h-5 text-text-secondary" />
          </button>
        </div>

        <div className="flex-1 overflow-y-auto p-6 space-y-4">
          {engagements.length === 0 ? (
            <p className="text-center text-text-secondary py-8">No {displayType.toLowerCase()}s found</p>
          ) : (
            engagements.map((engagement) => {
              const content = getEngagementContent(engagement);
              return (
                <div
                  key={engagement.id}
                  className="bg-surface rounded-lg p-4 border border-border"
                >
                  <div className="flex items-start justify-between mb-2">
                    <h3 className="font-medium text-text">{getEngagementTitle(engagement)}</h3>
                    <div className="flex items-center gap-1 text-xs text-text-secondary">
                      <Clock className="w-3 h-3" />
                      {formatTimestamp(engagement)}
                    </div>
                  </div>
                  {getEngagementDetails(engagement)}
                  {content && (
                    <div className="mt-3 pt-3 border-t border-border">
                      <p className="text-sm text-text whitespace-pre-wrap break-words">{content}</p>
                    </div>
                  )}
                  {!content && (
                    <Badge variant="default" className="text-xs mt-2">No content available</Badge>
                  )}
                </div>
              );
            })
          )}
        </div>
      </div>
    </div>
  );
}
