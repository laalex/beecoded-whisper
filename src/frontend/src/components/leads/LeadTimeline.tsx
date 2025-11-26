import { Card, CardContent, CardHeader, CardTitle } from '@/components/common/Card';
import { Badge } from '@/components/common/Badge';
import type { Interaction, InteractionType } from '@/types';
import { Mail, Phone, Calendar, FileText, MessageSquare, Linkedin, MessageCircle } from 'lucide-react';
import { format } from 'date-fns';

interface LeadTimelineProps {
  interactions: Interaction[];
}

const typeIcons: Record<InteractionType, typeof Mail> = {
  email: Mail,
  call: Phone,
  meeting: Calendar,
  note: FileText,
  task: MessageSquare,
  sms: MessageCircle,
  linkedin: Linkedin,
  other: MessageSquare,
};

const sentimentColors: Record<string, 'success' | 'default' | 'danger'> = {
  positive: 'success',
  neutral: 'default',
  negative: 'danger',
};

export function LeadTimeline({ interactions }: LeadTimelineProps) {
  if (!interactions || interactions.length === 0) {
    return (
      <Card>
        <CardHeader>
          <CardTitle>Activity Timeline</CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-text-secondary text-center py-8">
            No interactions recorded yet.
          </p>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>Activity Timeline</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-6">
          {interactions.map((interaction, index) => {
            const Icon = typeIcons[interaction.type] || MessageSquare;
            const isLast = index === interactions.length - 1;

            return (
              <div key={interaction.id} className="relative flex gap-4">
                {!isLast && (
                  <div className="absolute left-5 top-10 bottom-0 w-px bg-border" />
                )}

                <div className="w-10 h-10 rounded-full bg-surface flex items-center justify-center flex-shrink-0 z-10">
                  <Icon className="w-5 h-5 text-text-secondary" />
                </div>

                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2 mb-1">
                    <span className="font-medium text-text capitalize">
                      {interaction.type}
                    </span>
                    {interaction.direction && (
                      <Badge variant="default" className="text-xs">
                        {interaction.direction}
                      </Badge>
                    )}
                    {interaction.sentiment && (
                      <Badge
                        variant={sentimentColors[interaction.sentiment]}
                        className="text-xs"
                      >
                        {interaction.sentiment}
                      </Badge>
                    )}
                  </div>

                  {interaction.subject && (
                    <p className="text-sm font-medium text-text mb-1">
                      {interaction.subject}
                    </p>
                  )}

                  {interaction.summary && (
                    <p className="text-sm text-text-secondary line-clamp-2">
                      {interaction.summary}
                    </p>
                  )}

                  {!interaction.summary && interaction.content && (
                    <p className="text-sm text-text-secondary line-clamp-2">
                      {interaction.content}
                    </p>
                  )}

                  <p className="text-xs text-text-secondary mt-2">
                    {interaction.occurred_at
                      ? format(new Date(interaction.occurred_at), 'MMM d, yyyy · h:mm a')
                      : 'Date unknown'}
                    {interaction.user && ` · ${interaction.user.name}`}
                  </p>
                </div>
              </div>
            );
          })}
        </div>
      </CardContent>
    </Card>
  );
}
