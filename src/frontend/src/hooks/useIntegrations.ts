import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { integrationsApi } from '@/services/integrations';
import type { Integration } from '@/types';

export function useIntegrations() {
  return useQuery({
    queryKey: ['integrations'],
    queryFn: integrationsApi.list,
  });
}

export function useConnectHubSpot() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async () => {
      const { url } = await integrationsApi.connectHubSpot();
      return url;
    },
    onSuccess: () => {
      // Will refetch after OAuth callback
      queryClient.invalidateQueries({ queryKey: ['integrations'] });
    },
  });
}

export function useConnectGmail() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async () => {
      const { url } = await integrationsApi.connectGmail();
      return url;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['integrations'] });
    },
  });
}

export function useSyncIntegration() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (integrationId: number) => integrationsApi.sync(integrationId),
    onSuccess: () => {
      // Invalidate all related queries after sync
      queryClient.invalidateQueries({ queryKey: ['integrations'] });
      queryClient.invalidateQueries({ queryKey: ['leads'] });
      queryClient.invalidateQueries({ queryKey: ['dashboard'] });
    },
  });
}

export function useDisconnectIntegration() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (integrationId: number) => integrationsApi.disconnect(integrationId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['integrations'] });
    },
  });
}

export function useIntegrationByProvider(integrations: Integration[] | undefined, provider: 'hubspot' | 'gmail') {
  return integrations?.find((i) => i.provider === provider);
}
