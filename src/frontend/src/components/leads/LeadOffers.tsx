import { Card, CardContent, CardHeader, CardTitle } from '@/components/common/Card';
import { Badge } from '@/components/common/Badge';
import Button from '@/components/common/Button';
import type { Offer, OfferStatus } from '@/types';
import { FileText, Plus } from 'lucide-react';
import { format } from 'date-fns';

interface LeadOffersProps {
  offers: Offer[];
  onCreateOffer?: () => void;
}

const statusColors: Record<OfferStatus, 'default' | 'success' | 'warning' | 'danger' | 'info'> = {
  draft: 'default',
  sent: 'info',
  viewed: 'info',
  accepted: 'success',
  rejected: 'danger',
  expired: 'default',
};

export function LeadOffers({ offers, onCreateOffer }: LeadOffersProps) {
  const formatCurrency = (value: number, currency: string = 'USD') => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(value);
  };

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between">
        <CardTitle>Offers</CardTitle>
        {onCreateOffer && (
          <Button variant="ghost" size="sm" onClick={onCreateOffer}>
            <Plus className="w-4 h-4" />
          </Button>
        )}
      </CardHeader>
      <CardContent>
        {!offers || offers.length === 0 ? (
          <p className="text-text-secondary text-center py-4 text-sm">
            No offers created yet
          </p>
        ) : (
          <div className="space-y-3">
            {offers.map((offer) => (
              <div
                key={offer.id}
                className="flex items-center gap-3 p-3 bg-surface rounded-lg"
              >
                <div className="w-8 h-8 rounded bg-primary/10 flex items-center justify-center flex-shrink-0">
                  <FileText className="w-4 h-4 text-primary" />
                </div>

                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium text-text truncate">
                    {offer.title}
                  </p>
                  <p className="text-xs text-text-secondary">
                    {format(new Date(offer.created_at), 'MMM d, yyyy')}
                  </p>
                </div>

                <div className="text-right flex-shrink-0">
                  <p className="text-sm font-semibold text-text">
                    {formatCurrency(offer.amount, offer.currency)}
                  </p>
                  <Badge
                    variant={statusColors[offer.status]}
                    className="text-xs mt-1"
                  >
                    {offer.status}
                  </Badge>
                </div>
              </div>
            ))}
          </div>
        )}
      </CardContent>
    </Card>
  );
}
