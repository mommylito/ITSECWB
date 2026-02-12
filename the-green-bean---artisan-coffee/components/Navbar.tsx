
import React from 'react';
import { User } from '../types';

interface NavbarProps {
  user: User | null;
  currentView: string;
  onLogout: () => void;
  onNavigate: (view: any) => void;
}

const Navbar: React.FC<NavbarProps> = ({ user, currentView, onLogout, onNavigate }) => {
  return (
    <nav className="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-stone-200">
      <div className="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
        <div 
          className="flex items-center space-x-2 cursor-pointer" 
          onClick={() => onNavigate(user ? 'menu' : 'login')}
        >
          <div className="w-8 h-8 bg-emerald-800 rounded-full flex items-center justify-center text-white font-bold text-lg">G</div>
          <span className="font-serif text-xl tracking-tight text-stone-800 hidden sm:block">The Green Bean</span>
        </div>

        <div className="flex items-center space-x-6">
          {user ? (
            <>
              <button 
                onClick={() => onNavigate('menu')}
                className={`text-sm font-medium transition ${currentView === 'menu' ? 'text-emerald-800' : 'text-stone-600 hover:text-stone-900'}`}
              >
                Menu
              </button>
              {user.role === 'admin' && (
                <button 
                  onClick={() => onNavigate('admin')}
                  className={`text-sm font-medium transition ${currentView === 'admin' ? 'text-emerald-800' : 'text-stone-600 hover:text-stone-900'}`}
                >
                  Admin Panel
                </button>
              )}
              <div className="h-6 w-px bg-stone-200"></div>
              <div className="flex items-center space-x-3">
                <button 
                  onClick={() => onNavigate('profile')}
                  className="flex items-center space-x-2 group"
                >
                  <img 
                    src={user.profilePhoto || 'https://picsum.photos/40'} 
                    alt="Profile" 
                    className="w-8 h-8 rounded-full border border-stone-200 group-hover:border-emerald-800 transition"
                  />
                  <span className={`text-sm font-medium transition ${currentView === 'profile' ? 'text-emerald-800' : 'text-stone-600 group-hover:text-stone-900'}`}>
                    Profile
                  </span>
                </button>
                <button 
                  onClick={onLogout}
                  className="text-sm font-medium text-stone-600 hover:text-stone-900 ml-2"
                >
                  Logout
                </button>
              </div>
            </>
          ) : (
            <>
              <button 
                onClick={() => onNavigate('login')}
                className={`text-sm font-medium transition ${currentView === 'login' ? 'text-emerald-800' : 'text-stone-600 hover:text-stone-900'}`}
              >
                Login
              </button>
              <button 
                onClick={() => onNavigate('register')}
                className="px-4 py-2 bg-emerald-800 text-white text-sm font-medium rounded-full hover:bg-emerald-900 transition"
              >
                Join Now
              </button>
            </>
          )}
        </div>
      </div>
    </nav>
  );
};

export default Navbar;
