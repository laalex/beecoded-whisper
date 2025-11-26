import { Card, CardContent, CardHeader, CardTitle } from '@/components/common/Card';
import type { Lead } from '@/types';
import { Mail, Phone, Globe, Linkedin, Calendar, DollarSign, Tag } from 'lucide-react';
import { format } from 'date-fns';

interface LeadContactInfoProps {
  lead: Lead;
}

export function LeadContactInfo({ lead }: LeadContactInfoProps) {
  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle>Contact Information</CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        {lead.email && (
          <div className="flex items-center gap-3">
            <Mail className="w-4 h-4 text-text-secondary" />
            <a
              href={`mailto:${lead.email}`}
              className="text-primary hover:underline"
            >
              {lead.email}
            </a>
          </div>
        )}

        {lead.phone && (
          <div className="flex items-center gap-3">
            <Phone className="w-4 h-4 text-text-secondary" />
            <a
              href={`tel:${lead.phone}`}
              className="text-text hover:text-primary"
            >
              {lead.phone}
            </a>
          </div>
        )}

        {lead.website && (
          <div className="flex items-center gap-3">
            <Globe className="w-4 h-4 text-text-secondary" />
            <a
              href={lead.website}
              target="_blank"
              rel="noopener noreferrer"
              className="text-primary hover:underline truncate"
            >
              {lead.website.replace(/^https?:\/\//, '')}
            </a>
          </div>
        )}

        {lead.linkedin_url && (
          <div className="flex items-center gap-3">
            <Linkedin className="w-4 h-4 text-text-secondary" />
            <a
              href={lead.linkedin_url}
              target="_blank"
              rel="noopener noreferrer"
              className="text-primary hover:underline"
            >
              LinkedIn Profile
            </a>
          </div>
        )}

        <div className="border-t border-border pt-4 mt-4 space-y-3">
          {lead.estimated_value !== null && (
            <div className="flex items-center justify-between">
              <span className="flex items-center gap-2 text-text-secondary">
                <DollarSign className="w-4 h-4" />
                Estimated Value
              </span>
              <span className="font-semibold text-text">
                {formatCurrency(lead.estimated_value)}
              </span>
            </div>
          )}

          {lead.next_followup_at && (
            <div className="flex items-center justify-between">
              <span className="flex items-center gap-2 text-text-secondary">
                <Calendar className="w-4 h-4" />
                Next Follow-up
              </span>
              <span className="text-text">
                {format(new Date(lead.next_followup_at), 'MMM d, yyyy')}
              </span>
            </div>
          )}

          <div className="flex items-center justify-between">
            <span className="flex items-center gap-2 text-text-secondary">
              <Tag className="w-4 h-4" />
              Source
            </span>
            <span className="text-text capitalize">{lead.source}</span>
          </div>
        </div>

        {lead.tags && lead.tags.length > 0 && (
          <div className="border-t border-border pt-4 mt-4">
            <p className="text-sm text-text-secondary mb-2">Tags</p>
            <div className="flex flex-wrap gap-2">
              {lead.tags.map((tag, index) => (
                <span
                  key={index}
                  className="px-2 py-1 bg-surface rounded-md text-xs text-text"
                >
                  {tag}
                </span>
              ))}
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
