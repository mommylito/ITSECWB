
export interface User {
  id: number;
  fullName: string;
  email: string;
  profilePhoto: string;
  role: 'admin' | 'user';
  failedAttempts: number;
  lockoutUntil: number | null;
}

export interface MenuItem {
  id: number;
  name: string;
  price: number;
  description: string;
  category: string;
}

export type AuthState = {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
};
