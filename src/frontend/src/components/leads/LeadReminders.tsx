import { Card, CardContent, CardHeader, CardTitle } from '@/components/common/Card';
import { Badge } from '@/components/common/Badge';
import Button from '@/components/common/Button';
import type { Reminder } from '@/types';
import { Bell, Check, Plus, Clock } from 'lucide-react';
import { format, isPast, isToday, isTomorrow } from 'date-fns';

interface LeadRemindersProps {
  reminders: Reminder[];
  onComplete?: (id: number) => void;
  onAddReminder?: () => void;
}

const priorityColors: Record<string, 'default' | 'warning' | 'danger' | 'info'> = {
  low: 'default',
  medium: 'info',
  high: 'warning',
  urgent: 'danger',
};

export function LeadReminders({ reminders, onComplete, onAddReminder }: LeadRemindersProps) {
  const pendingReminders = reminders?.filter((r) => !r.completed_at) || [];

  const formatDueDate = (dueAt: string) => {
    const date = new Date(dueAt);
    if (isToday(date)) return 'Today';
    if (isTomorrow(date)) return 'Tomorrow';
    if (isPast(date)) return 'Overdue';
    return format(date, 'MMM d');
  };

  const isOverdue = (dueAt: string) => isPast(new Date(dueAt));

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between">
        <CardTitle>Reminders</CardTitle>
        {onAddReminder && (
          <Button variant="ghost" size="sm" onClick={onAddReminder}>
            <Plus className="w-4 h-4" />
          </Button>
        )}
      </CardHeader>
      <CardContent>
        {pendingReminders.length === 0 ? (
          <p className="text-text-secondary text-center py-4 text-sm">
            No upcoming reminders
          </p>
        ) : (
          <div className="space-y-3">
            {pendingReminders.map((reminder) => (
              <div
                key={reminder.id}
                className={`flex items-start gap-3 p-3 rounded-lg ${
                  isOverdue(reminder.due_at)
                    ? 'bg-red-50 border border-red-200'
                    : 'bg-surface'
                }`}
              >
                <div className="flex-shrink-0 mt-0.5">
                  {isOverdue(reminder.due_at) ? (
                    <Clock className="w-4 h-4 text-red-500" />
                  ) : (
                    <Bell className="w-4 h-4 text-text-secondary" />
                  )}
                </div>

                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium text-text truncate">
                    {reminder.title}
                  </p>
                  <div className="flex items-center gap-2 mt-1">
                    <span
                      className={`text-xs ${
                        isOverdue(reminder.due_at)
                          ? 'text-red-600 font-medium'
                          : 'text-text-secondary'
                      }`}
                    >
                      {formatDueDate(reminder.due_at)}
                    </span>
                    <Badge
                      variant={priorityColors[reminder.priority]}
                      className="text-xs"
                    >
                      {reminder.priority}
                    </Badge>
                  </div>
                </div>

                {onComplete && (
                  <button
                    onClick={() => onComplete(reminder.id)}
                    className="flex-shrink-0 p-1 hover:bg-green-100 rounded transition-colors cursor-pointer"
                    title="Mark as complete"
                  >
                    <Check className="w-4 h-4 text-text-secondary hover:text-green-600" />
                  </button>
                )}
              </div>
            ))}
          </div>
        )}
      </CardContent>
    </Card>
  );
}
