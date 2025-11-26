import { useEffect, useCallback } from 'react';
import {
  useIntegrations,
  useConnectHubSpot,
  useConnectGmail,
  useSyncIntegration,
  useDisconnectIntegration,
  useIntegrationByProvider,
} from '@/hooks/useIntegrations';
import { IntegrationCard } from '@/components/features/IntegrationCard';
import { Skeleton } from '@/components/common/Skeleton';
import { Card, CardContent } from '@/components/common/Card';
import { AlertCircle, Link as LinkIcon } from 'lucide-react';

export function Integrations() {
  const { data: integrations, isLoading, error, refetch } = useIntegrations();

  const connectHubSpot = useConnectHubSpot();
  const connectGmail = useConnectGmail();
  const syncIntegration = useSyncIntegration();
  const disconnectIntegration = useDisconnectIntegration();

  const hubspotIntegration = useIntegrationByProvider(integrations, 'hubspot');
  const gmailIntegration = useIntegrationByProvider(integrations, 'gmail');

  // Listen for OAuth popup messages
  useEffect(() => {
    const handleMessage = (event: MessageEvent) => {
      if (event.data?.type === 'oauth_success') {
        refetch();
      }
    };

    window.addEventListener('message', handleMessage);
    return () => window.removeEventListener('message', handleMessage);
  }, [refetch]);

  const handleConnectHubSpot = useCallback(async () => {
    try {
      const url = await connectHubSpot.mutateAsync();
      // Open OAuth in popup
      const popup = window.open(url, 'hubspot-oauth', 'width=600,height=700,scrollbars=yes');

      // Poll for popup close and refetch
      const pollTimer = setInterval(() => {
        if (popup?.closed) {
          clearInterval(pollTimer);
          refetch();
        }
      }, 1000);
    } catch (err) {
      console.error('Failed to connect HubSpot:', err);
    }
  }, [connectHubSpot, refetch]);

  const handleConnectGmail = useCallback(async () => {
    try {
      const url = await connectGmail.mutateAsync();
      const popup = window.open(url, 'gmail-oauth', 'width=600,height=700,scrollbars=yes');

      const pollTimer = setInterval(() => {
        if (popup?.closed) {
          clearInterval(pollTimer);
          refetch();
        }
      }, 1000);
    } catch (err) {
      console.error('Failed to connect Gmail:', err);
    }
  }, [connectGmail, refetch]);

  const handleSync = useCallback(
    (integrationId: number) => {
      syncIntegration.mutate(integrationId);
    },
    [syncIntegration]
  );

  const handleDisconnect = useCallback(
    (integrationId: number) => {
      disconnectIntegration.mutate(integrationId);
    },
    [disconnectIntegration]
  );

  if (isLoading) {
    return (
      <div className="p-6 space-y-6">
        <div>
          <Skeleton className="h-8 w-40 mb-2" />
          <Skeleton className="h-4 w-80" />
        </div>
        <div className="grid gap-6 md:grid-cols-2">
          {Array.from({ length: 2 }).map((_, i) => (
            <Card key={i}>
              <CardContent>
                <div className="flex items-start gap-4">
                  <Skeleton className="w-14 h-14 rounded-xl" />
                  <div className="flex-1 space-y-2">
                    <Skeleton className="h-5 w-24" />
                    <Skeleton className="h-4 w-48" />
                  </div>
                  <Skeleton className="h-5 w-20 rounded-full" />
                </div>
                <div className="mt-4 pt-4 border-t border-border">
                  <Skeleton className="h-9 w-32" />
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="p-6">
        <div className="bg-red-50 border border-red-200 rounded-lg p-4 flex items-center gap-3 text-red-700">
          <AlertCircle className="w-5 h-5" />
          <span>Failed to load integrations. Please try again.</span>
        </div>
      </div>
    );
  }

  return (
    <div className="p-6">
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-text flex items-center gap-2">
          <LinkIcon className="w-6 h-6" />
          Integrations
        </h1>
        <p className="text-text-secondary mt-1">
          Connect your CRM and email to sync leads and interactions automatically.
        </p>
      </div>

      <div className="grid gap-6 md:grid-cols-2">
        <IntegrationCard
          provider="hubspot"
          integration={hubspotIntegration}
          onConnect={handleConnectHubSpot}
          onSync={handleSync}
          onDisconnect={handleDisconnect}
          isConnecting={connectHubSpot.isPending}
          isSyncing={syncIntegration.isPending && syncIntegration.variables === hubspotIntegration?.id}
          isDisconnecting={disconnectIntegration.isPending && disconnectIntegration.variables === hubspotIntegration?.id}
        />

        <IntegrationCard
          provider="gmail"
          integration={gmailIntegration}
          onConnect={handleConnectGmail}
          onSync={handleSync}
          onDisconnect={handleDisconnect}
          isConnecting={connectGmail.isPending}
          isSyncing={syncIntegration.isPending && syncIntegration.variables === gmailIntegration?.id}
          isDisconnecting={disconnectIntegration.isPending && disconnectIntegration.variables === gmailIntegration?.id}
        />
      </div>

      <div className="mt-8 p-4 bg-surface-secondary rounded-lg">
        <h3 className="font-semibold text-text mb-2">How integrations work</h3>
        <ul className="text-sm text-text-secondary space-y-1 list-disc list-inside">
          <li>Connect your accounts using secure OAuth authentication</li>
          <li>Your credentials are never stored - only access tokens</li>
          <li>Sync contacts from HubSpot to import them as leads</li>
          <li>Gmail integration tracks email interactions with leads</li>
          <li>You can disconnect at any time to revoke access</li>
        </ul>
      </div>
    </div>
  );
}
