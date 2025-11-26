import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { leadsApi } from '@/services/leads';
import type { Lead, LeadStatus, InteractionType, AiAnalysis } from '@/types';

export function useLead(id: number | string) {
  const queryClient = useQueryClient();

  const query = useQuery({
    queryKey: ['lead', id],
    queryFn: () => leadsApi.getLead(id),
    enabled: !!id,
  });

  // Fetch history analysis separately (it's not included in lead response)
  const historyAnalysisQuery = useQuery({
    queryKey: ['lead', id, 'history-analysis'],
    queryFn: () => leadsApi.getHistoryAnalysis(id),
    enabled: !!id && query.data?.source === 'hubspot',
    retry: false, // Don't retry 404s
  });

  const updateMutation = useMutation({
    mutationFn: (data: Partial<Lead>) => leadsApi.updateLead(id, data),
    onMutate: async (newData) => {
      await queryClient.cancelQueries({ queryKey: ['lead', id] });
      const previousLead = queryClient.getQueryData<Lead>(['lead', id]);

      if (previousLead) {
        queryClient.setQueryData<Lead>(['lead', id], {
          ...previousLead,
          ...newData,
        });
      }

      return { previousLead };
    },
    onError: (_err, _newData, context) => {
      if (context?.previousLead) {
        queryClient.setQueryData(['lead', id], context.previousLead);
      }
    },
    onSettled: () => {
      queryClient.invalidateQueries({ queryKey: ['lead', id] });
      queryClient.invalidateQueries({ queryKey: ['leads'] });
    },
  });

  const updateStatusMutation = useMutation({
    mutationFn: (status: LeadStatus) => leadsApi.updateStatus(id, status),
    onMutate: async (newStatus) => {
      await queryClient.cancelQueries({ queryKey: ['lead', id] });
      const previousLead = queryClient.getQueryData<Lead>(['lead', id]);

      if (previousLead) {
        queryClient.setQueryData<Lead>(['lead', id], {
          ...previousLead,
          status: newStatus,
        });
      }

      return { previousLead };
    },
    onError: (_err, _newStatus, context) => {
      if (context?.previousLead) {
        queryClient.setQueryData(['lead', id], context.previousLead);
      }
    },
    onSettled: () => {
      queryClient.invalidateQueries({ queryKey: ['lead', id] });
      queryClient.invalidateQueries({ queryKey: ['leads'] });
    },
  });

  const addInteractionMutation = useMutation({
    mutationFn: (data: {
      type: InteractionType;
      content: string;
      subject?: string;
      direction?: 'inbound' | 'outbound';
      sentiment?: 'positive' | 'neutral' | 'negative';
    }) => leadsApi.addInteraction(Number(id), data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['lead', id] });
    },
  });

  const syncHubSpotMutation = useMutation({
    mutationFn: () => leadsApi.syncHubSpot(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['lead', id] });
    },
  });

  const analyzeMutation = useMutation({
    mutationFn: () => leadsApi.analyzeLead(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['lead', id] });
    },
  });

  const analyzeHistoryMutation = useMutation({
    mutationFn: () => leadsApi.analyzeHistory(id),
    onSuccess: (data: AiAnalysis) => {
      queryClient.setQueryData(['lead', id, 'history-analysis'], data);
      queryClient.invalidateQueries({ queryKey: ['lead', id] });
    },
  });

  return {
    lead: query.data,
    historyAnalysis: historyAnalysisQuery.data,
    isLoading: query.isLoading,
    isError: query.isError,
    error: query.error,
    updateLead: updateMutation.mutate,
    updateStatus: updateStatusMutation.mutate,
    addInteraction: addInteractionMutation.mutate,
    syncHubSpot: syncHubSpotMutation.mutate,
    analyzeLead: analyzeMutation.mutate,
    analyzeHistory: analyzeHistoryMutation.mutate,
    isUpdating: updateMutation.isPending || updateStatusMutation.isPending,
    isAddingInteraction: addInteractionMutation.isPending,
    isSyncing: syncHubSpotMutation.isPending,
    isAnalyzing: analyzeMutation.isPending,
    isAnalyzingHistory: analyzeHistoryMutation.isPending,
  };
}
