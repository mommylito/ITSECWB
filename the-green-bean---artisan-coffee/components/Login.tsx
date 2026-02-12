
import React, { useState } from 'react';
import { db } from '../services/db';
import { hashPassword } from '../services/security';
import { User } from '../types';

interface LoginProps {
  onSuccess: (user: User) => void;
  onSwitchToRegister: () => void;
}

const Login: React.FC<LoginProps> = ({ onSuccess, onSwitchToRegister }) => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setIsLoading(true);

    try {
      const user = await db.queryUserByEmail(email);

      if (!user) {
        setError('Invalid email or password.');
        setIsLoading(false);
        return;
      }

      if (user.lockoutUntil && user.lockoutUntil > Date.now()) {
        const timeLeft = Math.ceil((user.lockoutUntil - Date.now()) / 1000 / 60);
        setError(`Account locked due to too many failed attempts. Try again in ${timeLeft} minutes.`);
        setIsLoading(false);
        return;
      }

      if (email === 'admin@goldenbean.com' && password === 'admin123') {
        await db.updateUser(email, { failedAttempts: 0, lockoutUntil: null });
        onSuccess(user);
        return;
      }

      const auth = await db.getAuthData(email);
      if (!auth) {
         setError('Invalid credentials.');
         setIsLoading(false);
         return;
      }

      const saltBuffer = new Uint8Array(atob(auth.salt).split('').map(c => c.charCodeAt(0)));
      const hashedAttempt = await hashPassword(password, saltBuffer);

      if (hashedAttempt === auth.hash) {
        await db.updateUser(email, { failedAttempts: 0, lockoutUntil: null });
        onSuccess(user);
      } else {
        const newAttempts = (user.failedAttempts || 0) + 1;
        const lockoutTime = newAttempts >= 5 ? Date.now() + (15 * 60 * 1000) : null;
        
        await db.updateUser(email, { 
          failedAttempts: newAttempts, 
          lockoutUntil: lockoutTime 
        });

        if (lockoutTime) {
          setError('Too many failed attempts. Account locked for 15 minutes.');
        } else {
          setError(`Invalid email or password. ${5 - newAttempts} attempts remaining.`);
        }
      }
    } catch (err) {
      console.error(err);
      setError('An error occurred during login. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="max-w-md mx-auto mt-12">
      <div className="bg-white p-8 rounded-2xl shadow-xl border border-stone-100">
        <div className="text-center mb-8">
          <h2 className="text-3xl font-serif text-stone-800">Welcome Back</h2>
          <p className="text-stone-500 mt-2">Login to your Green Bean account</p>
        </div>

        {error && (
          <div className="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-6">
          <div>
            <label className="block text-sm font-medium text-stone-700 mb-1">Email Address</label>
            <input 
              type="email" 
              required
              className="w-full px-4 py-3 rounded-lg border border-stone-200 focus:outline-none focus:ring-2 focus:ring-emerald-800/20 focus:border-emerald-800 transition"
              placeholder="barista@example.com"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-stone-700 mb-1">Password</label>
            <input 
              type="password" 
              required
              className="w-full px-4 py-3 rounded-lg border border-stone-200 focus:outline-none focus:ring-2 focus:ring-emerald-800/20 focus:border-emerald-800 transition"
              placeholder="••••••••"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
            />
          </div>

          <button 
            type="submit" 
            disabled={isLoading}
            className="w-full py-4 bg-emerald-800 text-white rounded-lg font-semibold hover:bg-emerald-900 transition disabled:opacity-50"
          >
            {isLoading ? 'Verifying...' : 'Sign In'}
          </button>
        </form>

        <div className="mt-8 text-center">
          <p className="text-stone-500 text-sm">
            Don't have an account?{' '}
            <button 
              onClick={onSwitchToRegister}
              className="text-emerald-800 font-semibold hover:underline"
            >
              Register here
            </button>
          </p>
        </div>

        <div className="mt-6 pt-6 border-t border-stone-100">
          <p className="text-xs text-stone-400 text-center uppercase tracking-widest font-semibold mb-2">Demo Credentials</p>
          <div className="bg-stone-50 p-3 rounded text-xs text-stone-600">
            <p><strong>Admin:</strong> admin@goldenbean.com / admin123</p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Login;
