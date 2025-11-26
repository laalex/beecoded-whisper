import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { Badge } from '@/components/common/Badge';
import Button from '@/components/common/Button';
import Input from '@/components/common/Input';
import { Skeleton } from '@/components/common/Skeleton';
import api from '@/services/api';
import type { Lead, PaginatedResponse } from '@/types';
import { Search, Plus, ChevronRight, User, TrendingUp } from 'lucide-react';
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
    if (value >= 1000000) return '$' + (value / 1000000).toFixed(1) + 'M';
    if (value >= 1000) return '$' + (value / 1000).toFixed(0) + 'k';
    return '$' + value;
  };

  const getScoreColor = (score: number) => {
    if (score >= 80) return 'text-green-600 bg-green-50';
    if (score >= 50) return 'text-yellow-600 bg-yellow-50';
    return 'text-gray-600 bg-gray-50';
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-text">Leads</h1>
          <p className="text-text-secondary text-sm">Manage your sales pipeline</p>
        </div>
        <Link to="/leads/new">
          <Button className="cursor-pointer">
            <Plus className="w-4 h-4 mr-2" />
            Add Lead
          </Button>
        </Link>
      </div>

      <div className="flex gap-3">
        <div className="flex-1">
          <Input
            placeholder="Search leads..."
            icon={<Search className="w-4 h-4" />}
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="h-9"
          />
        </div>
        <select
          className="px-3 py-2 text-sm border border-border rounded-lg bg-background text-text focus:outline-none focus:ring-2 focus:ring-primary cursor-pointer"
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

      {isLoading ? (
        <div className="space-y-2">
          {Array.from({ length: 8 }).map((_, i) => (
            <div key={i} className="flex items-center gap-3 p-3 bg-white rounded-lg border border-border">
              <Skeleton variant="circular" className="w-9 h-9 flex-shrink-0" />
              <div className="flex-1 min-w-0">
                <Skeleton className="h-4 w-40 mb-1" />
                <Skeleton className="h-3 w-28" />
              </div>
              <Skeleton className="h-5 w-16" />
              <Skeleton className="h-4 w-12" />
              <Skeleton className="h-4 w-16 hidden md:block" />
              <Skeleton className="h-4 w-4" />
            </div>
          ))}
        </div>
      ) : data?.data.length === 0 ? (
        <div className="bg-white rounded-xl border border-border p-12 text-center">
          <User className="w-12 h-12 mx-auto text-text-secondary mb-3" />
          <p className="text-text-secondary mb-4">No leads found</p>
          <Link to="/leads/new">
            <Button variant="secondary" className="cursor-pointer">
              Add your first lead
            </Button>
          </Link>
        </div>
      ) : (
        <div className="bg-white rounded-xl border border-border overflow-hidden">
          <div className="divide-y divide-border">
            {data?.data.map((lead) => (
              <Link
                key={lead.id}
                to={'/leads/' + lead.id}
                className="flex items-center gap-3 px-4 py-3 hover:bg-surface transition-colors cursor-pointer group"
              >
                <div className="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-white text-sm font-medium flex-shrink-0">
                  {lead.first_name.charAt(0)}
                </div>

                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium text-text truncate">
                    {lead.first_name} {lead.last_name}
                  </p>
                  <p className="text-xs text-text-secondary truncate">
                    {lead.company || 'No company'}{lead.job_title ? ` · ${lead.job_title}` : ''}
                  </p>
                </div>

                <Badge variant={statusColors[lead.status] || 'default'} className="flex-shrink-0">
                  {lead.status}
                </Badge>

                <div className={`flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium flex-shrink-0 ${getScoreColor(lead.score)}`}>
                  <TrendingUp className="w-3 h-3" />
                  {lead.score}
                </div>

                <div className="text-right flex-shrink-0 hidden md:block w-20">
                  {lead.estimated_value ? (
                    <p className="text-sm font-medium text-text">
                      {formatCurrency(lead.estimated_value)}
                    </p>
                  ) : (
                    <p className="text-xs text-text-secondary">-</p>
                  )}
                </div>

                <div className="text-right flex-shrink-0 hidden lg:block w-20">
                  <p className="text-xs text-text-secondary">
                    {format(new Date(lead.created_at), 'MMM d')}
                  </p>
                </div>

                <ChevronRight className="w-4 h-4 text-text-secondary group-hover:text-primary transition-colors flex-shrink-0" />
              </Link>
            ))}
          </div>
        </div>
      )}

      {data && data.total > data.per_page && (
        <div className="flex items-center justify-between text-sm text-text-secondary">
          <span>
            Showing {data.data.length} of {data.total} leads
          </span>
          <div className="flex gap-2">
            {data.current_page > 1 && (
              <Button variant="ghost" size="sm" className="cursor-pointer">
                Previous
              </Button>
            )}
            {data.current_page < data.last_page && (
              <Button variant="ghost" size="sm" className="cursor-pointer">
                Next
              </Button>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
