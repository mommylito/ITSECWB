
import React, { useState, useEffect } from 'react';
import { User, AuthState } from './types';
import Login from './components/Login';
import Registration from './components/Registration';
import AdminPanel from './components/AdminPanel';
import Menu from './components/Menu';
import Navbar from './components/Navbar';
import Profile from './components/Profile';

const App: React.FC = () => {
  const [view, setView] = useState<'login' | 'register' | 'menu' | 'admin' | 'profile'>('login');
  const [auth, setAuth] = useState<AuthState>({
    user: null,
    isAuthenticated: false,
    isLoading: true,
  });

  useEffect(() => {
    // Check for existing session
    const savedUser = localStorage.getItem('gb_session');
    if (savedUser) {
      setAuth({
        user: JSON.parse(savedUser),
        isAuthenticated: true,
        isLoading: false,
      });
      setView('menu');
    } else {
      setAuth(prev => ({ ...prev, isLoading: false }));
    }
  }, []);

  const handleLoginSuccess = (user: User) => {
    setAuth({ user, isAuthenticated: true, isLoading: false });
    localStorage.setItem('gb_session', JSON.stringify(user));
    setView('menu');
  };

  const handleLogout = () => {
    setAuth({ user: null, isAuthenticated: false, isLoading: false });
    localStorage.removeItem('gb_session');
    setView('login');
  };

  const handleProfileUpdate = (updatedUser: User) => {
    setAuth(prev => ({ ...prev, user: updatedUser }));
    localStorage.setItem('gb_session', JSON.stringify(updatedUser));
  };

  if (auth.isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-stone-100">
        <div className="animate-pulse text-emerald-800 font-serif text-2xl">Brewing freshness...</div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-stone-50">
      <Navbar 
        user={auth.user} 
        onLogout={handleLogout} 
        onNavigate={setView} 
        currentView={view} 
      />
      
      <main className="max-w-6xl mx-auto px-4 py-8">
        {view === 'login' && (
          <Login 
            onSuccess={handleLoginSuccess} 
            onSwitchToRegister={() => setView('register')} 
          />
        )}
        
        {view === 'register' && (
          <Registration 
            onSuccess={() => setView('login')} 
            onSwitchToLogin={() => setView('login')} 
          />
        )}
        
        {view === 'menu' && (
          <Menu user={auth.user} />
        )}
        
        {view === 'admin' && auth.user?.role === 'admin' && (
          <AdminPanel />
        )}

        {view === 'profile' && auth.user && (
          <Profile 
            user={auth.user} 
            onUpdate={handleProfileUpdate} 
          />
        )}

        {view === 'admin' && auth.user?.role !== 'admin' && (
          <div className="text-center py-20">
            <h2 className="text-3xl font-serif text-red-800">Access Denied</h2>
            <p className="mt-4 text-stone-600">Only baristas with admin keys can enter the vault.</p>
            <button 
              onClick={() => setView('menu')}
              className="mt-6 px-6 py-2 bg-emerald-800 text-white rounded-full hover:bg-emerald-700 transition"
            >
              Back to Menu
            </button>
          </div>
        )}
      </main>
      
      <footer className="border-t border-stone-200 mt-20 py-10 bg-white">
        <div className="max-w-6xl mx-auto px-4 text-center">
          <p className="font-serif text-xl text-stone-800">The Green Bean</p>
          <p className="text-stone-500 text-sm mt-2">Sustainable Roastery & Secure Digital Ordering</p>
          <p className="text-stone-400 text-xs mt-8">© 2024 Secure Coffee Systems. All rights reserved.</p>
        </div>
      </footer>
    </div>
  );
};

export default App;
