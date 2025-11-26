export interface User {
  id: number;
  name: string;
  email: string;
  roles: Role[];
  permissions: Permission[];
}

export interface Role {
  id: number;
  name: string;
}

export interface Permission {
  id: number;
  name: string;
}

export interface Lead {
  id: number;
  user_id: number;
  assigned_to: number | null;
  external_id: string | null;
  source: string;
  first_name: string;
  last_name: string | null;
  email: string | null;
  phone: string | null;
  company: string | null;
  job_title: string | null;
  website: string | null;
  linkedin_url: string | null;
  status: LeadStatus;
  estimated_value: number | null;
  score: number;
  tags: string[] | null;
  custom_fields: Record<string, unknown> | null;
  notes: string | null;
  last_contacted_at: string | null;
  next_followup_at: string | null;
  created_at: string;
  updated_at: string;
  assignee?: User;
  score_details?: LeadScore;
}

export type LeadStatus = 
  | 'new' 
  | 'contacted' 
  | 'qualified' 
  | 'proposal' 
  | 'negotiation' 
  | 'won' 
  | 'lost' 
  | 'dormant';

export interface LeadScore {
  id: number;
  lead_id: number;
  total_score: number;
  engagement_score: number;
  fit_score: number;
  behavior_score: number;
  recency_score: number;
  conversion_probability: number;
  factors: ScoreFactor[];
}

export interface ScoreFactor {
  type: 'positive' | 'negative' | 'warning';
  message: string;
}

export interface Interaction {
  id: number;
  lead_id: number;
  user_id: number;
  type: InteractionType;
  direction: 'inbound' | 'outbound' | null;
  subject: string | null;
  content: string | null;
  summary: string | null;
  sentiment: 'positive' | 'neutral' | 'negative' | null;
  occurred_at: string | null;
  user?: User;
}

export type InteractionType = 
  | 'email' 
  | 'call' 
  | 'meeting' 
  | 'note' 
  | 'task' 
  | 'sms' 
  | 'linkedin' 
  | 'other';

export interface Reminder {
  id: number;
  user_id: number;
  lead_id: number | null;
  title: string;
  description: string | null;
  type: ReminderType;
  priority: 'low' | 'medium' | 'high' | 'urgent';
  due_at: string;
  completed_at: string | null;
  is_ai_generated: boolean;
  lead?: Lead;
}

export type ReminderType = 
  | 'followup' 
  | 'task' 
  | 'meeting' 
  | 'call' 
  | 'email' 
  | 'custom';

export interface Integration {
  id: number;
  provider: 'hubspot' | 'gmail';
  provider_email: string | null;
  is_active: boolean;
  last_synced_at: string | null;
}

export interface DashboardStats {
  total_leads: number;
  new_leads_today: number;
  hot_leads: number;
  avg_response_time: number;
  conversion_rate: number;
  pipeline_value: number;
  upcoming_reminders: Reminder[];
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}
