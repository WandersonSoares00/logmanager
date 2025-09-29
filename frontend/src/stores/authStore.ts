import { create } from 'zustand';

interface AuthState {
  token: string | null;
  isAuthenticated: boolean;
  login: (token: string) => void;
  logout: () => void;
}

export const useAuthStore = create<AuthState>((set) => ({
  token: localStorage.getItem('authToken'),
  isAuthenticated: !!localStorage.getItem('authToken'),
  login: (token: string) => {
    localStorage.setItem('authToken', token);
    set({ token: token, isAuthenticated: true });
  },
  logout: () => {
    localStorage.removeItem('authToken');
    set({ token: null, isAuthenticated: false });
  },
}));