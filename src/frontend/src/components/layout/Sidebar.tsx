import { Link, useLocation } from 'react-router-dom';
import { cn } from '@/utils/cn';
import {
  LayoutDashboard,
  Users,
  Mail,
  Calendar,
  FileText,
  Settings,
  Mic,
  Link as LinkIcon,
  Bell,
  LogOut,
} from 'lucide-react';
import { useAuthStore } from '@/stores/authStore';
import { Logo } from '@/components/common/Logo';

const navigation = [
  { name: 'Dashboard', href: '/dashboard', icon: LayoutDashboard },
  { name: 'Leads', href: '/leads', icon: Users },
  { name: 'Sequences', href: '/sequences', icon: Mail },
  { name: 'Reminders', href: '/reminders', icon: Bell },
  { name: 'Offers', href: '/offers', icon: FileText },
  { name: 'Voice Input', href: '/voice', icon: Mic },
  { name: 'Integrations', href: '/integrations', icon: LinkIcon },
  { name: 'Settings', href: '/settings', icon: Settings },
];

export function Sidebar() {
  const location = useLocation();
  const { user, logout } = useAuthStore();

  return (
    <div className="flex flex-col h-full w-64 bg-primary text-white">
      {/* Logo */}
      <div className="flex items-center h-16 px-4 border-b border-primary-light">
        <Logo size="sm" variant="dark" />
      </div>

      {/* Navigation */}
      <nav className="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        {navigation.map((item) => {
          const isActive = location.pathname.startsWith(item.href);
          return (
            <Link
              key={item.name}
              to={item.href}
              className={cn(
                'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
                isActive
                  ? 'bg-accent text-primary'
                  : 'text-white/80 hover:bg-primary-light hover:text-white'
              )}
            >
              <item.icon className="w-5 h-5 mr-3" />
              {item.name}
            </Link>
          );
        })}
      </nav>

      {/* User section */}
      <div className="p-4 border-t border-primary-light">
        <div className="flex items-center mb-4">
          <div className="w-10 h-10 rounded-full bg-accent flex items-center justify-center text-primary font-semibold">
            {user?.name?.charAt(0).toUpperCase()}
          </div>
          <div className="ml-3">
            <p className="text-sm font-medium">{user?.name}</p>
            <p className="text-xs text-white/60">{user?.roles?.[0]?.name}</p>
          </div>
        </div>
        <button
          onClick={() => logout()}
          className="flex items-center w-full px-4 py-2 text-sm text-white/80 hover:text-white hover:bg-primary-light rounded-lg transition-colors"
        >
          <LogOut className="w-4 h-4 mr-2" />
          Sign out
        </button>
      </div>
    </div>
  );
}
