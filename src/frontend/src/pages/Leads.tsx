import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { Card, CardContent } from '@/components/common/Card';
import { Badge } from '@/components/common/Badge';
import Button from '@/components/common/Button';
import Input from '@/components/common/Input';
import api from '@/services/api';
import type { Lead, PaginatedResponse } from '@/types';
import { Search, Plus, ChevronRight, User } from 'lucide-react';
import { format } from 'date-fns';

const statusColors: Record<string, 'default' | 'success' | 'warning' | 'danger' | 'info'> = {
  new: 'info',
  contacted: 'default',
  qualified: 'success',
  proposal: 'warning',
  negotiation: 'warning',
  won: 'success',
  lost: 'danger',
  dormant: 'default',
};

export function Leads() {
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');

  const { data, isLoading } = useQuery<PaginatedResponse<Lead>>({
    queryKey: ['leads', search, status],
    queryFn: async () => {
      const params = new URLSearchParams();
      if (search) params.append('search', search);
      if (status) params.append('status', status);
      const response = await api.get('/leads?' + params.toString());
      return response.data;
    },
  });

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-text">Leads</h1>
          <p className="text-text-secondary mt-1">Manage your sales pipeline</p>
        </div>
        <Link to="/leads/new">
          <Button>
            <Plus className="w-4 h-4 mr-2" />
            Add Lead
          </Button>
        </Link>
      </div>

      <Card padding="sm">
        <CardContent>
          <div className="flex gap-4">
            <div className="flex-1">
              <Input
                placeholder="Search leads..."
                icon={<Search className="w-5 h-5" />}
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>
            <select
              className="px-4 py-2.5 border border-border rounded-lg bg-background text-text focus:outline-none focus:ring-2 focus:ring-primary"
              value={status}
              onChange={(e) => setStatus(e.target.value)}
            >
              <option value="">All Status</option>
              <option value="new">New</option>
              <option value="contacted">Contacted</option>
              <option value="qualified">Qualified</option>
              <option value="proposal">Proposal</option>
              <option value="negotiation">Negotiation</option>
              <option value="won">Won</option>
              <option value="lost">Lost</option>
            </select>
          </div>
        </CardContent>
      </Card>

      {isLoading ? (
        <div className="space-y-4">
          {[...Array(5)].map((_, i) => (
            <div key={i} className="h-24 bg-surface rounded-xl animate-pulse"></div>
          ))}
        </div>
      ) : (
        <div className="space-y-4">
          {data?.data.map((lead) => (
            <Link key={lead.id} to={'/leads/' + lead.id}>
              <Card className="hover:border-primary transition-colors cursor-pointer">
                <CardContent className="flex items-center justify-between">
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-white font-semibold">
                      {lead.first_name.charAt(0)}
                    </div>
                    <div>
                      <p className="font-semibold text-text">
                        {lead.first_name} {lead.last_name}
                      </p>
                      <p className="text-sm text-text-secondary">
                        {lead.company || 'No company'} {lead.job_title ? '- ' + lead.job_title : ''}
                      </p>
                    </div>
                  </div>
                  <div className="flex items-center gap-6">
                    <div className="text-right">
                      <Badge variant={statusColors[lead.status] || 'default'}>
                        {lead.status}
                      </Badge>
                      <p className="text-sm text-text-secondary mt-1">
                        Score: {lead.score}
                      </p>
                    </div>
                    <div className="text-right hidden md:block">
                      {lead.estimated_value && (
                        <p className="font-semibold text-text">
                          {formatCurrency(lead.estimated_value)}
                        </p>
                      )}
                      <p className="text-sm text-text-secondary">
                        {format(new Date(lead.created_at), 'MMM d, yyyy')}
                      </p>
                    </div>
                    <ChevronRight className="w-5 h-5 text-text-secondary" />
                  </div>
                </CardContent>
              </Card>
            </Link>
          ))}

          {data?.data.length === 0 && (
            <Card>
              <CardContent className="text-center py-12">
                <User className="w-12 h-12 mx-auto text-text-secondary mb-4" />
                <p className="text-text-secondary">No leads found</p>
                <Link to="/leads/new">
                  <Button variant="secondary" className="mt-4">
                    Add your first lead
                  </Button>
                </Link>
              </CardContent>
            </Card>
          )}
        </div>
      )}
    </div>
  );
}
