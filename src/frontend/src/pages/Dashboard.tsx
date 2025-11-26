import { useQuery } from '@tanstack/react-query';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/common/Card';
import { Badge } from '@/components/common/Badge';
import api from '@/services/api';
import type { DashboardStats } from '@/types';
import { Users, TrendingUp, Clock, DollarSign, Bell, Target } from 'lucide-react';
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
      <div className="animate-pulse space-y-6">
        <div className="h-8 bg-surface rounded w-1/4"></div>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {[...Array(4)].map((_, i) => (
            <div key={i} className="h-32 bg-surface rounded-xl"></div>
          ))}
        </div>
      </div>
    );
  }

  const statCards = [
    { label: 'Total Leads', value: stats?.total_leads || 0, icon: Users, color: 'text-blue-600' },
    { label: 'Hot Leads', value: stats?.hot_leads || 0, icon: Target, color: 'text-orange-600' },
    { label: 'Conversion Rate', value: (stats?.conversion_rate || 0) + '%', icon: TrendingUp, color: 'text-green-600' },
    { label: 'Pipeline Value', value: '$' + (stats?.pipeline_value || 0).toLocaleString(), icon: DollarSign, color: 'text-primary' },
  ];

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl font-bold text-text">Dashboard</h1>
        <p className="text-text-secondary mt-1">Welcome back! Here is your sales overview.</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {statCards.map((stat) => (
          <Card key={stat.label}>
            <CardContent className="flex items-center justify-between">
              <div>
                <p className="text-sm text-text-secondary">{stat.label}</p>
                <p className="text-2xl font-bold text-text mt-1">{stat.value}</p>
              </div>
              <div className={'p-3 rounded-full bg-surface ' + stat.color}>
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
            <div className="text-center py-8">
              <p className="text-4xl font-bold text-primary">
                {stats?.avg_response_time || 0} min
              </p>
              <p className="text-text-secondary mt-2">Target: 10 minutes</p>
              {(stats?.avg_response_time || 0) <= 10 ? (
                <Badge variant="success" className="mt-4">On Target</Badge>
              ) : (
                <Badge variant="warning" className="mt-4">Needs Improvement</Badge>
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
              <ul className="space-y-3">
                {stats.upcoming_reminders.slice(0, 5).map((reminder) => (
                  <li key={reminder.id} className="flex items-center justify-between p-3 bg-surface rounded-lg">
                    <div>
                      <p className="font-medium text-text">{reminder.title}</p>
                      <p className="text-sm text-text-secondary">
                        {format(new Date(reminder.due_at), 'MMM d, h:mm a')}
                      </p>
                    </div>
                    <Badge variant={
                      reminder.priority === 'urgent' ? 'danger' :
                      reminder.priority === 'high' ? 'warning' : 'default'
                    }>
                      {reminder.priority}
                    </Badge>
                  </li>
                ))}
              </ul>
            ) : (
              <p className="text-center text-text-secondary py-8">No upcoming reminders</p>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
