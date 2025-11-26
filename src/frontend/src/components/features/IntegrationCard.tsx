import { useState } from 'react';
import { Card, CardContent } from '@/components/common/Card';
import Button from '@/components/common/Button';
import { Badge } from '@/components/common/Badge';
import { RefreshCw, Unlink, ExternalLink, CheckCircle } from 'lucide-react';
import type { Integration } from '@/types';

interface IntegrationCardProps {
  provider: 'hubspot' | 'gmail';
  integration?: Integration;
  onConnect: () => void;
  onSync?: (id: number) => void;
  onDisconnect?: (id: number) => void;
  isConnecting?: boolean;
  isSyncing?: boolean;
  isDisconnecting?: boolean;
}

const providerConfig = {
  hubspot: {
    name: 'HubSpot',
    description: 'Sync contacts and deals from HubSpot CRM',
    icon: (
      <svg viewBox="0 0 24 24" className="w-8 h-8" fill="currentColor">
        <path d="M18.164 7.93V5.084a2.198 2.198 0 001.267-1.984 2.21 2.21 0 00-4.42 0c0 .873.52 1.608 1.266 1.984V7.93a6.156 6.156 0 00-3.234 1.67l-6.53-5.076a2.17 2.17 0 00.107-.67 2.21 2.21 0 10-2.21 2.21c.401 0 .774-.11 1.097-.3l6.394 4.97a6.142 6.142 0 00-.758 2.954c0 1.09.284 2.115.783 3.006l-2.012 2.012a1.954 1.954 0 00-.623-.107 1.978 1.978 0 101.978 1.978c0-.222-.038-.434-.107-.632l1.987-1.987a6.174 6.174 0 003.473 1.07c3.416 0 6.187-2.77 6.187-6.186a6.175 6.175 0 00-4.644-5.974zm-3.066 9.083a3.097 3.097 0 110-6.194 3.097 3.097 0 010 6.194z" />
      </svg>
    ),
    color: 'text-orange-500',
    bgColor: 'bg-orange-50',
  },
  gmail: {
    name: 'Gmail',
    description: 'Import email conversations and track interactions',
    icon: (
      <svg viewBox="0 0 24 24" className="w-8 h-8" fill="currentColor">
        <path d="M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.273H1.636A1.636 1.636 0 010 19.366V5.457c0-2.023 2.309-3.178 3.927-1.964L5.455 4.64 12 9.548l6.545-4.91 1.528-1.145C21.69 2.28 24 3.434 24 5.457z" />
      </svg>
    ),
    color: 'text-red-500',
    bgColor: 'bg-red-50',
  },
};

export function IntegrationCard({
  provider,
  integration,
  onConnect,
  onSync,
  onDisconnect,
  isConnecting = false,
  isSyncing = false,
  isDisconnecting = false,
}: IntegrationCardProps) {
  const [showDisconnectConfirm, setShowDisconnectConfirm] = useState(false);
  const config = providerConfig[provider];
  const isConnected = integration?.is_active;

  const formatLastSynced = (dateStr: string | null) => {
    if (!dateStr) return 'Never';
    const date = new Date(dateStr);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins} min ago`;
    if (diffHours < 24) return `${diffHours} hours ago`;
    return `${diffDays} days ago`;
  };

  const handleDisconnect = () => {
    if (integration && onDisconnect) {
      onDisconnect(integration.id);
      setShowDisconnectConfirm(false);
    }
  };

  return (
    <Card className="hover:shadow-md transition-shadow">
      <CardContent>
        <div className="flex items-start justify-between">
          <div className="flex items-center gap-4">
            <div className={`p-3 rounded-xl ${config.bgColor} ${config.color}`}>
              {config.icon}
            </div>
            <div>
              <h3 className="text-lg font-semibold text-text">{config.name}</h3>
              <p className="text-sm text-text-secondary">{config.description}</p>
            </div>
          </div>

          <Badge variant={isConnected ? 'success' : 'default'}>
            {isConnected ? (
              <span className="flex items-center gap-1">
                <CheckCircle className="w-3 h-3" />
                Connected
              </span>
            ) : (
              'Not connected'
            )}
          </Badge>
        </div>

        {isConnected && integration && (
          <div className="mt-4 pt-4 border-t border-border">
            <div className="flex items-center justify-between text-sm">
              <div className="text-text-secondary">
                <span className="font-medium">Account:</span> {integration.provider_email || 'Unknown'}
              </div>
              <div className="text-text-secondary">
                <span className="font-medium">Last synced:</span> {formatLastSynced(integration.last_synced_at)}
              </div>
            </div>
          </div>
        )}

        <div className="mt-4 flex items-center gap-2">
          {!isConnected ? (
            <Button onClick={onConnect} isLoading={isConnecting} className="flex items-center gap-2">
              <ExternalLink className="w-4 h-4" />
              Connect {config.name}
            </Button>
          ) : (
            <>
              <Button
                variant="outline"
                onClick={() => integration && onSync?.(integration.id)}
                isLoading={isSyncing}
                className="flex items-center gap-2"
              >
                <RefreshCw className={`w-4 h-4 ${isSyncing ? 'animate-spin' : ''}`} />
                Sync Now
              </Button>

              {!showDisconnectConfirm ? (
                <Button
                  variant="ghost"
                  onClick={() => setShowDisconnectConfirm(true)}
                  className="flex items-center gap-2 text-red-600 hover:bg-red-50"
                >
                  <Unlink className="w-4 h-4" />
                  Disconnect
                </Button>
              ) : (
                <div className="flex items-center gap-2">
                  <span className="text-sm text-text-secondary">Confirm?</span>
                  <Button
                    variant="ghost"
                    size="sm"
                    onClick={handleDisconnect}
                    isLoading={isDisconnecting}
                    className="text-red-600"
                  >
                    Yes
                  </Button>
                  <Button variant="ghost" size="sm" onClick={() => setShowDisconnectConfirm(false)}>
                    No
                  </Button>
                </div>
              )}
            </>
          )}
        </div>
      </CardContent>
    </Card>
  );
}
