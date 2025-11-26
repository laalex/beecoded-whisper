import { Link } from 'react-router-dom';
import { Badge } from '@/components/common/Badge';
import Button from '@/components/common/Button';
import type { Lead, LeadStatus } from '@/types';
import { ArrowLeft, Edit2, Trash2, Phone, Mail, MessageSquare, Bell } from 'lucide-react';

interface LeadHeaderProps {
  lead: Lead;
  onStatusChange: (status: LeadStatus) => void;
  onQuickAction: (action: 'call' | 'email' | 'note' | 'reminder') => void;
  isUpdating?: boolean;
}

const statusColors: Record<LeadStatus, 'default' | 'success' | 'warning' | 'danger' | 'info'> = {
  new: 'info',
  contacted: 'default',
  qualified: 'success',
  proposal: 'warning',
  negotiation: 'warning',
  won: 'success',
  lost: 'danger',
  dormant: 'default',
};

const statuses: LeadStatus[] = [
  'new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost', 'dormant'
];

export function LeadHeader({ lead, onStatusChange, onQuickAction, isUpdating }: LeadHeaderProps) {
  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <Link
          to="/leads"
          className="inline-flex items-center text-text-secondary hover:text-text transition-colors"
        >
          <ArrowLeft className="w-4 h-4 mr-2" />
          Back to Leads
        </Link>
        <div className="flex items-center gap-2">
          <Button variant="ghost" size="sm">
            <Edit2 className="w-4 h-4 mr-2" />
            Edit
          </Button>
          <Button variant="ghost" size="sm" className="text-red-500 hover:text-red-600">
            <Trash2 className="w-4 h-4" />
          </Button>
        </div>
      </div>

      <div className="flex items-start justify-between">
        <div className="flex items-center gap-4">
          <div className="w-16 h-16 rounded-full bg-primary flex items-center justify-center text-white text-2xl font-semibold">
            {lead.first_name.charAt(0)}
            {lead.last_name?.charAt(0) || ''}
          </div>
          <div>
            <h1 className="text-2xl font-bold text-text">
              {lead.first_name} {lead.last_name}
            </h1>
            <p className="text-text-secondary">
              {lead.company && <span>{lead.company}</span>}
              {lead.company && lead.job_title && <span className="mx-2">·</span>}
              {lead.job_title && <span>{lead.job_title}</span>}
            </p>
          </div>
        </div>

        <div className="flex items-center gap-4">
          <select
            value={lead.status}
            onChange={(e) => onStatusChange(e.target.value as LeadStatus)}
            disabled={isUpdating}
            className="px-3 py-2 border border-border rounded-lg bg-background text-text focus:outline-none focus:ring-2 focus:ring-primary disabled:opacity-50"
          >
            {statuses.map((status) => (
              <option key={status} value={status}>
                {status.charAt(0).toUpperCase() + status.slice(1)}
              </option>
            ))}
          </select>
          <Badge variant={statusColors[lead.status]} className="text-sm px-3 py-1">
            {lead.status}
          </Badge>
        </div>
      </div>

      <div className="flex items-center gap-2">
        <Button variant="secondary" size="sm" onClick={() => onQuickAction('call')}>
          <Phone className="w-4 h-4 mr-2" />
          Log Call
        </Button>
        <Button variant="secondary" size="sm" onClick={() => onQuickAction('email')}>
          <Mail className="w-4 h-4 mr-2" />
          Log Email
        </Button>
        <Button variant="secondary" size="sm" onClick={() => onQuickAction('note')}>
          <MessageSquare className="w-4 h-4 mr-2" />
          Add Note
        </Button>
        <Button variant="secondary" size="sm" onClick={() => onQuickAction('reminder')}>
          <Bell className="w-4 h-4 mr-2" />
          Set Reminder
        </Button>
      </div>
    </div>
  );
}
