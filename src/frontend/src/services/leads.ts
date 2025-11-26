import api from './api';
import type { Lead, LeadStatus, Interaction, InteractionType, EnrichmentData, AiAnalysis } from '@/types';

export const leadsApi = {
  getLead: async (id: number | string): Promise<Lead> => {
    const response = await api.get(`/leads/${id}`);
    return response.data;
  },

  updateLead: async (id: number | string, data: Partial<Lead>): Promise<Lead> => {
    const response = await api.put(`/leads/${id}`, data);
    return response.data;
  },

  updateStatus: async (id: number | string, status: LeadStatus): Promise<Lead> => {
    const response = await api.put(`/leads/${id}`, { status });
    return response.data;
  },

  addInteraction: async (
    leadId: number,
    data: {
      type: InteractionType;
      content: string;
      subject?: string;
      direction?: 'inbound' | 'outbound';
      sentiment?: 'positive' | 'neutral' | 'negative';
    }
  ): Promise<Interaction> => {
    const response = await api.post('/interactions', {
      lead_id: leadId,
      ...data,
      occurred_at: new Date().toISOString(),
    });
    return response.data;
  },

  getHistory: async (leadId: number | string): Promise<Interaction[]> => {
    const response = await api.get(`/leads/${leadId}/history`);
    return response.data;
  },

  syncHubSpot: async (leadId: number | string): Promise<{ enrichment_data: EnrichmentData }> => {
    const response = await api.post(`/leads/${leadId}/sync`);
    return response.data;
  },

  analyzeLead: async (leadId: number | string): Promise<{ analysis: AiAnalysis }> => {
    const response = await api.post(`/leads/${leadId}/analyze`);
    return response.data;
  },
};
