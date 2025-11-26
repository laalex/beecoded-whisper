import api from './api';
import type { Integration } from '@/types';

export const integrationsApi = {
  list: async (): Promise<Integration[]> => {
    const response = await api.get('/integrations');
    return response.data;
  },

  connectHubSpot: async (): Promise<{ url: string }> => {
    const response = await api.post('/integrations/hubspot/connect');
    return response.data;
  },

  connectGmail: async (): Promise<{ url: string }> => {
    const response = await api.post('/integrations/gmail/connect');
    return response.data;
  },

  sync: async (integrationId: number): Promise<{ message: string }> => {
    const response = await api.post(`/integrations/${integrationId}/sync`);
    return response.data;
  },

  disconnect: async (integrationId: number): Promise<{ message: string }> => {
    const response = await api.delete(`/integrations/${integrationId}`);
    return response.data;
  },
};
