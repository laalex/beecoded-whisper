import { Card, CardContent, CardHeader, CardTitle } from '@/components/common/Card';
import { Badge } from '@/components/common/Badge';
import Button from '@/components/common/Button';
import type { EnrichmentData } from '@/types';
import { RefreshCw, Building, Users, DollarSign, AlertCircle, CheckCircle } from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';

interface LeadHubSpotDataProps {
  enrichmentData: EnrichmentData | null | undefined;
  onSync?: () => void;
  isSyncing?: boolean;
}

export function LeadHubSpotData({ enrichmentData, onSync, isSyncing }: LeadHubSpotDataProps) {
  if (!enrichmentData || enrichmentData.provider !== 'hubspot') {
    return null;
  }

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value);
  };

  const lifecycleStageColors: Record<string, 'default' | 'info' | 'success' | 'warning'> = {
    subscriber: 'default',
    lead: 'info',
    marketingqualifiedlead: 'info',
    salesqualifiedlead: 'success',
    opportunity: 'success',
    customer: 'success',
    evangelist: 'warning',
  };

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between">
        <div className="flex items-center gap-2">
          <CardTitle>HubSpot Data</CardTitle>
          {enrichmentData.sync_error ? (
            <Badge variant="danger" className="text-xs">
              <AlertCircle className="w-3 h-3 mr-1" />
              Sync Error
            </Badge>
          ) : enrichmentData.last_synced_at ? (
            <Badge variant="success" className="text-xs">
              <CheckCircle className="w-3 h-3 mr-1" />
              Synced {formatDistanceToNow(new Date(enrichmentData.last_synced_at), { addSuffix: true })}
            </Badge>
          ) : null}
        </div>
        {onSync && (
          <Button variant="ghost" size="sm" onClick={onSync} disabled={isSyncing}>
            <RefreshCw className={`w-4 h-4 ${isSyncing ? 'animate-spin' : ''}`} />
          </Button>
        )}
      </CardHeader>
      <CardContent className="space-y-4">
        {enrichmentData.sync_error && (
          <div className="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
            {enrichmentData.sync_error}
          </div>
        )}

        {enrichmentData.hubspot_lifecycle_stage && (
          <div className="flex items-center justify-between">
            <span className="text-text-secondary text-sm">Lifecycle Stage</span>
            <Badge variant={lifecycleStageColors[enrichmentData.hubspot_lifecycle_stage] || 'default'}>
              {enrichmentData.hubspot_lifecycle_stage.replace(/([A-Z])/g, ' $1').trim()}
            </Badge>
          </div>
        )}

        {enrichmentData.industry && (
          <div className="flex items-center justify-between">
            <span className="flex items-center gap-2 text-text-secondary text-sm">
              <Building className="w-4 h-4" />
              Industry
            </span>
            <span className="text-text">{enrichmentData.industry}</span>
          </div>
        )}

        {enrichmentData.employee_count && (
          <div className="flex items-center justify-between">
            <span className="flex items-center gap-2 text-text-secondary text-sm">
              <Users className="w-4 h-4" />
              Employees
            </span>
            <span className="text-text">{enrichmentData.employee_count.toLocaleString()}</span>
          </div>
        )}

        {enrichmentData.annual_revenue && (
          <div className="flex items-center justify-between">
            <span className="flex items-center gap-2 text-text-secondary text-sm">
              <DollarSign className="w-4 h-4" />
              Annual Revenue
            </span>
            <span className="text-text font-medium">{formatCurrency(enrichmentData.annual_revenue)}</span>
          </div>
        )}

        {enrichmentData.hubspot_owner && (
          <div className="border-t border-border pt-4">
            <p className="text-sm text-text-secondary mb-2">HubSpot Owner</p>
            <p className="text-text">
              {enrichmentData.hubspot_owner.first_name} {enrichmentData.hubspot_owner.last_name}
            </p>
            {enrichmentData.hubspot_owner.email && (
              <p className="text-sm text-text-secondary">{enrichmentData.hubspot_owner.email}</p>
            )}
          </div>
        )}

        {enrichmentData.hubspot_deals && enrichmentData.hubspot_deals.length > 0 && (
          <div className="border-t border-border pt-4">
            <p className="text-sm font-medium text-text mb-3">Associated Deals</p>
            <div className="space-y-2">
              {enrichmentData.hubspot_deals.map((deal) => (
                <div key={deal.id} className="bg-surface rounded-lg p-3">
                  <p className="text-sm font-medium text-text">{deal.name || 'Unnamed Deal'}</p>
                  <div className="flex items-center justify-between mt-1">
                    {deal.amount && (
                      <span className="text-sm text-text-secondary">{formatCurrency(deal.amount)}</span>
                    )}
                    {deal.stage && (
                      <Badge variant="default" className="text-xs">{deal.stage}</Badge>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
