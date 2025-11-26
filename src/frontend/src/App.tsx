import { useEffect } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useAuthStore } from '@/stores/authStore';
import { Layout } from '@/components/layout/Layout';
import { Login } from '@/pages/Login';
import { Register } from '@/pages/Register';
import { Dashboard } from '@/pages/Dashboard';
import { Leads } from '@/pages/Leads';
import { LeadDetail } from '@/pages/LeadDetail';
import { Integrations } from '@/pages/Integrations';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 1000 * 60 * 5,
      retry: 1,
    },
  },
});

function AppContent() {
  const { fetchUser, isAuthenticated, isInitialized } = useAuthStore();

  useEffect(() => {
    fetchUser();
  }, [fetchUser]);

  // Show loading state until we know the authentication status
  if (!isInitialized) {
    return (
      <div className="flex h-screen items-center justify-center bg-surface">
        <div className="text-center">
          <div className="w-8 h-8 border-2 border-primary border-t-transparent rounded-full animate-spin mx-auto mb-2" />
          <p className="text-text-secondary text-sm">Loading...</p>
        </div>
      </div>
    );
  }

  return (
    <Routes>
      <Route path="/login" element={!isAuthenticated ? <Login /> : <Navigate to="/dashboard" />} />
      <Route path="/register" element={!isAuthenticated ? <Register /> : <Navigate to="/dashboard" />} />
      <Route path="/" element={<Layout />}>
        <Route index element={<Navigate to="/dashboard" replace />} />
        <Route path="dashboard" element={<Dashboard />} />
        <Route path="leads" element={<Leads />} />
        <Route path="leads/new" element={<div>New Lead (Coming Soon)</div>} />
        <Route path="leads/:id" element={<LeadDetail />} />
        <Route path="sequences" element={<div>Sequences (Coming Soon)</div>} />
        <Route path="reminders" element={<div>Reminders (Coming Soon)</div>} />
        <Route path="offers" element={<div>Offers (Coming Soon)</div>} />
        <Route path="voice" element={<div>Voice Input (Coming Soon)</div>} />
        <Route path="integrations" element={<Integrations />} />
        <Route path="settings" element={<div>Settings (Coming Soon)</div>} />
      </Route>
    </Routes>
  );
}

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <AppContent />
      </BrowserRouter>
    </QueryClientProvider>
  );
}

export default App;
