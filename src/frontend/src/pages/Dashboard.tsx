import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/common/Card';
import { Badge } from '@/components/common/Badge';
import { Skeleton } from '@/components/common/Skeleton';
import api from '@/services/api';
import type { DashboardStats } from '@/types';
import { Users, TrendingUp, Clock, DollarSign, Bell, Target, ChevronRight } from 'lucide-react';
import { format } from 'date-fns';

export function Dashboard() {
  const { data: stats, isLoading } = useQuery<DashboardStats>({
    queryKey: ['dashboard'],
    queryFn: async () => {
      const response = await api.get('/dashboard');
      return response.data;
    },
  });

  if (isLoading) {
    return (
      <div className="space-y-6">
        <div>
          <Skeleton className="h-8 w-32 mb-2" />
          <Skeleton className="h-4 w-64" />
        </div>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          {Array.from({ length: 4 }).map((_, i) => (
            <Card key={i} padding="md">
              <div className="flex items-center justify-between">
                <div className="space-y-2">
                  <Skeleton className="h-3 w-20" />
                  <Skeleton className="h-7 w-16" />
                </div>
                <Skeleton variant="circular" className="w-12 h-12" />
              </div>
            </Card>
          ))}
        </div>
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <Card>
            <CardContent>
              <Skeleton className="h-5 w-40 mb-6" />
              <div className="text-center py-6">
                <Skeleton className="h-12 w-24 mx-auto mb-2" />
                <Skeleton className="h-4 w-32 mx-auto" />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent>
              <Skeleton className="h-5 w-40 mb-4" />
              <div className="space-y-3">
                {Array.from({ length: 3 }).map((_, i) => (
                  <div key={i} className="flex items-center gap-3 p-3 bg-surface rounded-lg">
                    <Skeleton className="h-4 flex-1" />
                    <Skeleton className="h-5 w-16" />
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    );
  }

  const statCards = [
    { label: 'Total Leads', value: stats?.total_leads || 0, icon: Users, color: 'text-blue-600', bgColor: 'bg-blue-50' },
    { label: 'Hot Leads', value: stats?.hot_leads || 0, icon: Target, color: 'text-orange-600', bgColor: 'bg-orange-50' },
    { label: 'Conversion Rate', value: (stats?.conversion_rate || 0) + '%', icon: TrendingUp, color: 'text-green-600', bgColor: 'bg-green-50' },
    { label: 'Pipeline Value', value: '$' + (stats?.pipeline_value || 0).toLocaleString(), icon: DollarSign, color: 'text-primary', bgColor: 'bg-primary/10' },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-text">Dashboard</h1>
        <p className="text-text-secondary text-sm">Welcome back! Here's your sales overview.</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {statCards.map((stat) => (
          <Card key={stat.label} padding="md" className="hover:shadow-md transition-shadow">
            <CardContent className="flex items-center justify-between">
              <div>
                <p className="text-sm text-text-secondary">{stat.label}</p>
                <p className="text-2xl font-bold text-text mt-1">{stat.value}</p>
              </div>
              <div className={`p-3 rounded-full ${stat.bgColor} ${stat.color}`}>
                <stat.icon className="w-6 h-6" />
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center">
              <Clock className="w-5 h-5 mr-2 text-primary" />
              Average Response Time
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-center py-6">
              <p className="text-4xl font-bold text-primary">
                {stats?.avg_response_time || 0} <span className="text-lg font-normal text-text-secondary">min</span>
              </p>
              <p className="text-text-secondary text-sm mt-2">Target: 10 minutes</p>
              {(stats?.avg_response_time || 0) <= 10 ? (
                <Badge variant="success" className="mt-3">On Target</Badge>
              ) : (
                <Badge variant="warning" className="mt-3">Needs Improvement</Badge>
              )}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center">
              <Bell className="w-5 h-5 mr-2 text-primary" />
              Upcoming Reminders
            </CardTitle>
          </CardHeader>
          <CardContent>
            {stats?.upcoming_reminders && stats.upcoming_reminders.length > 0 ? (
              <ul className="space-y-2">
                {stats.upcoming_reminders.slice(0, 5).map((reminder) => (
                  <li
                    key={reminder.id}
                    className="flex items-center justify-between p-3 bg-surface rounded-lg hover:bg-surface/80 transition-colors cursor-pointer"
                  >
                    <div className="min-w-0 flex-1">
                      <p className="font-medium text-text text-sm truncate">{reminder.title}</p>
                      <p className="text-xs text-text-secondary">
                        {format(new Date(reminder.due_at), 'MMM d, h:mm a')}
                      </p>
                    </div>
                    <div className="flex items-center gap-2 flex-shrink-0">
                      <Badge variant={
                        reminder.priority === 'urgent' ? 'danger' :
                        reminder.priority === 'high' ? 'warning' : 'default'
                      }>
                        {reminder.priority}
                      </Badge>
                      <ChevronRight className="w-4 h-4 text-text-secondary" />
                    </div>
                  </li>
                ))}
              </ul>
            ) : (
              <p className="text-center text-text-secondary py-8 text-sm">No upcoming reminders</p>
            )}
            {stats?.upcoming_reminders && stats.upcoming_reminders.length > 0 && (
              <Link
                to="/reminders"
                className="block text-center text-sm text-primary hover:underline mt-3 cursor-pointer"
              >
                View all reminders
              </Link>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
