import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import api from '@/services/api';
import type { User } from '@/types';

interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  isInitialized: boolean;
  login: (email: string, password: string) => Promise<void>;
  register: (name: string, email: string, password: string, passwordConfirmation: string) => Promise<void>;
  logout: () => Promise<void>;
  fetchUser: () => Promise<void>;
  hasPermission: (permission: string) => boolean;
  hasRole: (role: string) => boolean;
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set, get) => ({
      user: null,
      token: null,
      isAuthenticated: false,
      isLoading: false,
      isInitialized: false,

      login: async (email: string, password: string) => {
        set({ isLoading: true });
        try {
          const response = await api.post('/auth/login', { email, password });
          const { user, token } = response.data;
          localStorage.setItem('auth_token', token);
          set({ user, token, isAuthenticated: true, isLoading: false });
        } catch (error) {
          set({ isLoading: false });
          throw error;
        }
      },

      register: async (name: string, email: string, password: string, passwordConfirmation: string) => {
        set({ isLoading: true });
        try {
          const response = await api.post('/auth/register', {
            name,
            email,
            password,
            password_confirmation: passwordConfirmation,
          });
          const { user, token } = response.data;
          localStorage.setItem('auth_token', token);
          set({ user, token, isAuthenticated: true, isLoading: false });
        } catch (error) {
          set({ isLoading: false });
          throw error;
        }
      },

      logout: async () => {
        try {
          await api.post('/auth/logout');
        } finally {
          localStorage.removeItem('auth_token');
          set({ user: null, token: null, isAuthenticated: false });
        }
      },

      fetchUser: async () => {
        const token = localStorage.getItem('auth_token');
        if (!token) {
          set({ isAuthenticated: false, isInitialized: true });
          return;
        }
        try {
          const response = await api.get('/auth/me');
          set({ user: response.data.user, isAuthenticated: true, isInitialized: true });
        } catch {
          localStorage.removeItem('auth_token');
          set({ user: null, token: null, isAuthenticated: false, isInitialized: true });
        }
      },

      hasPermission: (permission: string) => {
        const { user } = get();
        if (!user) return false;
        return user.permissions?.some(p => p.name === permission) ?? false;
      },

      hasRole: (role: string) => {
        const { user } = get();
        if (!user) return false;
        return user.roles?.some(r => r.name === role) ?? false;
      },
    }),
    {
      name: 'auth-storage',
      partialize: (state) => ({ token: state.token }),
    }
  )
);
