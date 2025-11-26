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
  is_vip: boolean;
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
  interactions?: Interaction[];
  reminders?: Reminder[];
  offers?: Offer[];
  enrichment_data?: EnrichmentData;
  ai_analysis?: AiAnalysis;
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

export interface Offer {
  id: number;
  lead_id: number;
  user_id: number;
  title: string;
  description: string | null;
  amount: number;
  currency: string;
  status: OfferStatus;
  valid_until: string | null;
  sent_at: string | null;
  viewed_at: string | null;
  responded_at: string | null;
  created_at: string;
}

export type OfferStatus =
  | 'draft'
  | 'sent'
  | 'viewed'
  | 'accepted'
  | 'rejected'
  | 'expired';

export interface EnrichmentData {
  id: number;
  lead_id: number;
  provider: string;
  company_data: Record<string, unknown> | null;
  contact_data: Record<string, unknown> | null;
  industry: string | null;
  employee_count: number | null;
  annual_revenue: number | null;
  hubspot_lifecycle_stage: string | null;
  hubspot_deals: HubSpotDeal[] | null;
  hubspot_activities: HubSpotActivity[] | null;
  hubspot_owner: HubSpotOwner | null;
  last_synced_at: string | null;
  sync_error: string | null;
}

export interface HubSpotDeal {
  id: string;
  name: string | null;
  amount: number | null;
  stage: string | null;
  close_date: string | null;
}

export interface HubSpotActivity {
  type: string;
  timestamp: number;
}

export interface HubSpotOwner {
  id: string;
  email: string | null;
  first_name: string | null;
  last_name: string | null;
}

export interface AiAnalysis {
  id: number;
  lead_id: number;
  analysis_type: 'full' | 'scoring' | 'nurturing' | 'sentiment' | 'history';
  insights: AiInsights | null;
  recommendations: AiRecommendation[] | null;
  risks: AiRisk[] | null;
  opportunities: AiOpportunity[] | null;
  confidence_score: number | null;
  model_used: string;
  analyzed_at: string | null;
}

// Separate type for history analysis with different insights structure
export interface HistoryAiAnalysis extends Omit<AiAnalysis, 'insights' | 'analysis_type'> {
  analysis_type: 'history';
  insights: HistoryAnalysisInsights | null;
}

export interface AiInsights {
  summary: string;
  engagement_level: 'high' | 'medium' | 'low';
  engagement_trend: 'improving' | 'stable' | 'declining';
  relationship_health: 'strong' | 'good' | 'fair' | 'weak';
  deal_stage_fit: boolean;
  key_interests: string[];
  communication_preference: string;
  best_contact_time: string;
}

export interface AiRecommendation {
  action: string;
  type: 'call' | 'email' | 'meeting' | 'task';
  priority: 'high' | 'medium' | 'low';
  timing: 'immediate' | 'this_week' | 'next_week';
  rationale: string;
}

export interface AiRisk {
  factor: string;
  severity: 'high' | 'medium' | 'low';
  description: string;
  mitigation: string;
}

export interface AiOpportunity {
  type: 'upsell' | 'cross_sell' | 'referral' | 'expansion' | 'buying_signal';
  description: string;
  potential_value: string;
}

// History Analysis Types
export interface HistoryAnalysisInsights {
  history_summary: HistorySummary | null;
  communication_patterns: CommunicationPatterns | null;
  relationship_timeline: RelationshipTimeline | null;
  key_topics_discussed: TopicDiscussed[] | null;
  buying_signals: BuyingSignal[] | null;
  objections_raised: Objection[] | null;
  deal_prediction: DealPrediction | null;
  insights: {
    summary: string;
    engagement_trend: 'improving' | 'stable' | 'declining' | 'unknown';
    notable_patterns?: string[];
    recommended_approach?: string;
  } | null;
}

export interface HistorySummary {
  total_engagements: number;
  time_span_days?: number;
  engagement_types_breakdown?: Record<string, number>;
  average_response_time_hours?: number | null;
  engagement_quality?: 'high' | 'medium' | 'low' | 'minimal' | 'unknown';
}

export interface CommunicationPatterns {
  preferred_channel: string | null;
  most_active_day?: string | null;
  most_active_time?: string | null;
  response_pattern?: string | null;
  engagement_frequency?: string | null;
}

export interface RelationshipTimeline {
  phases: RelationshipPhase[];
  current_phase?: string;
  relationship_health?: 'strong' | 'good' | 'fair' | 'weak' | 'at_risk';
}

export interface RelationshipPhase {
  period: string;
  phase: string;
  key_events?: string[];
  sentiment?: 'positive' | 'neutral' | 'negative';
}

export interface TopicDiscussed {
  topic: string;
  frequency: number;
  sentiment?: 'positive' | 'neutral' | 'negative';
  last_discussed?: string;
}

export interface BuyingSignal {
  signal: string;
  date?: string;
  strength: 'strong' | 'moderate' | 'weak';
  context?: string;
}

export interface Objection {
  objection: string;
  date?: string;
  status: 'addressed' | 'unresolved' | 'partially_addressed';
  resolution?: string | null;
}

export interface DealPrediction {
  likelihood_to_close: number;
  estimated_close_timeframe?: string;
  confidence: number;
  key_factors?: string[];
  risks_to_close?: string[];
}

export interface HubSpotHistoryResponse {
  engagements: HubSpotEngagement[];
  summary: {
    total: number;
    fetched: number;
    by_type: Record<string, number>;
    time_span_days: number;
    first_engagement: string | null;
    last_engagement: string | null;
  };
}

export interface HubSpotEngagement {
  id: string;
  type: string;
  timestamp: number | null;
  created_at: string | null;
  metadata: HubSpotEngagementMetadata;
}

export interface HubSpotEmailParticipant {
  raw?: string;
  email?: string;
  firstName?: string;
  lastName?: string;
}

export interface HubSpotEngagementMetadata {
  subject?: string | null;
  from?: string | HubSpotEmailParticipant | null;
  to?: (string | HubSpotEmailParticipant)[];
  text?: string | null;
  body?: string | null;
  title?: string | null;
  duration_seconds?: number | null;
  status?: string | null;
}

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
