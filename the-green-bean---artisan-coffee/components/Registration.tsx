
import React, { useState, useRef } from 'react';
import { db } from '../services/db';
import { 
  hashPassword, 
  generateSalt, 
  validateEmail, 
  isValidImageFile 
} from '../services/security';

interface RegistrationProps {
  onSuccess: () => void;
  onSwitchToLogin: () => void;
}

const Registration: React.FC<RegistrationProps> = ({ onSuccess, onSwitchToLogin }) => {
  const [formData, setFormData] = useState({
    fullName: '',
    email: '',
    password: '',
  });
  const [profilePhoto, setProfilePhoto] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const handleFileChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    setError(null);
    const valid = await isValidImageFile(file);
    if (!valid) {
      setError('Invalid file type. Only JPEG and PNG images are allowed.');
      if (fileInputRef.current) fileInputRef.current.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onloadend = () => {
      setProfilePhoto(reader.result as string);
    };
    reader.readAsDataURL(file);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setIsLoading(true);

    if (!validateEmail(formData.email)) {
      setError('Please enter a valid email address.');
      setIsLoading(false);
      return;
    }

    if (formData.password.length < 8) {
      setError('Password must be at least 8 characters long.');
      setIsLoading(false);
      return;
    }

    try {
      const existing = await db.queryUserByEmail(formData.email);
      if (existing) {
        setError('An account with this email already exists.');
        setIsLoading(false);
        return;
      }

      const salt = generateSalt();
      const hash = await hashPassword(formData.password, salt);
      const saltStr = btoa(String.fromCharCode(...salt));

      await db.createUser({
        fullName: formData.fullName,
        email: formData.email,
        profilePhoto: profilePhoto || 'https://picsum.photos/200',
        role: 'user',
        failedAttempts: 0,
        lockoutUntil: null,
      }, {
        salt: saltStr,
        hash: hash
      });

      alert('Registration successful! Please login.');
      onSuccess();
    } catch (err) {
      console.error(err);
      setError('An error occurred during registration.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="max-w-xl mx-auto mt-8">
      <div className="bg-white p-8 rounded-2xl shadow-xl border border-stone-100">
        <div className="text-center mb-8">
          <h2 className="text-3xl font-serif text-stone-800">Create Account</h2>
          <p className="text-stone-500 mt-2">Join the Green Bean artisan community</p>
        </div>

        {error && (
          <div className="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-5">
          <div className="flex flex-col items-center mb-6">
            <div className="w-24 h-24 bg-stone-100 rounded-full border-2 border-dashed border-stone-300 flex items-center justify-center overflow-hidden mb-3 relative group">
              {profilePhoto ? (
                <img src={profilePhoto} alt="Preview" className="w-full h-full object-cover" />
              ) : (
                <span className="text-stone-400 text-xs text-center px-2">No Photo</span>
              )}
              <div 
                onClick={() => fileInputRef.current?.click()}
                className="absolute inset-0 bg-black/40 text-white opacity-0 group-hover:opacity-100 flex items-center justify-center cursor-pointer transition"
              >
                <span className="text-xs font-bold">Change</span>
              </div>
            </div>
            <input 
              type="file" 
              ref={fileInputRef}
              onChange={handleFileChange}
              accept="image/*"
              className="hidden"
            />
            <p className="text-xs text-stone-400">Profile photo will be validated (JPG/PNG only)</p>
          </div>

          <div>
            <label className="block text-sm font-medium text-stone-700 mb-1">Full Name</label>
            <input 
              type="text" 
              required
              className="w-full px-4 py-2.5 rounded-lg border border-stone-200 focus:outline-none focus:ring-2 focus:ring-emerald-800/20 focus:border-emerald-800 transition"
              value={formData.fullName}
              onChange={(e) => setFormData({...formData, fullName: e.target.value})}
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-stone-700 mb-1">Email Address</label>
            <input 
              type="email" 
              required
              className="w-full px-4 py-2.5 rounded-lg border border-stone-200 focus:outline-none focus:ring-2 focus:ring-emerald-800/20 focus:border-emerald-800 transition"
              value={formData.email}
              onChange={(e) => setFormData({...formData, email: e.target.value})}
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-stone-700 mb-1">Password</label>
            <input 
              type="password" 
              required
              minLength={8}
              className="w-full px-4 py-3 rounded-lg border border-stone-200 focus:outline-none focus:ring-2 focus:ring-emerald-800/20 focus:border-emerald-800 transition"
              placeholder="Min. 8 characters"
              value={formData.password}
              onChange={(e) => setFormData({...formData, password: e.target.value})}
            />
          </div>

          <button 
            type="submit" 
            disabled={isLoading}
            className="w-full py-4 bg-emerald-800 text-white rounded-lg font-semibold hover:bg-emerald-900 transition disabled:opacity-50"
          >
            {isLoading ? 'Processing...' : 'Complete Registration'}
          </button>
        </form>

        <div className="mt-8 text-center">
          <p className="text-stone-500 text-sm">
            Already have an account?{' '}
            <button 
              onClick={onSwitchToLogin}
              className="text-emerald-800 font-semibold hover:underline"
            >
              Sign in
            </button>
          </p>
        </div>
      </div>
    </div>
  );
};

export default Registration;
