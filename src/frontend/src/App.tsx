import { useEffect } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useAuthStore } from '@/stores/authStore';
import { Layout } from '@/components/layout/Layout';
import { Login } from '@/pages/Login';
import { Register } from '@/pages/Register';
import { Dashboard } from '@/pages/Dashboard';
import { Leads } from '@/pages/Leads';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 1000 * 60 * 5,
      retry: 1,
    },
  },
});

function AppContent() {
  const { fetchUser, isAuthenticated } = useAuthStore();

  useEffect(() => {
    fetchUser();
  }, [fetchUser]);

  return (
    <Routes>
      <Route path="/login" element={!isAuthenticated ? <Login /> : <Navigate to="/dashboard" />} />
      <Route path="/register" element={!isAuthenticated ? <Register /> : <Navigate to="/dashboard" />} />
      <Route path="/" element={<Layout />}>
        <Route index element={<Navigate to="/dashboard" replace />} />
        <Route path="dashboard" element={<Dashboard />} />
        <Route path="leads" element={<Leads />} />
        <Route path="leads/:id" element={<div>Lead Detail (Coming Soon)</div>} />
        <Route path="leads/new" element={<div>New Lead (Coming Soon)</div>} />
        <Route path="sequences" element={<div>Sequences (Coming Soon)</div>} />
        <Route path="reminders" element={<div>Reminders (Coming Soon)</div>} />
        <Route path="offers" element={<div>Offers (Coming Soon)</div>} />
        <Route path="voice" element={<div>Voice Input (Coming Soon)</div>} />
        <Route path="integrations" element={<div>Integrations (Coming Soon)</div>} />
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
