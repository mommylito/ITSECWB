
import React, { useState, useRef } from 'react';
import { db } from '../services/db';
import { User } from '../types';
import { isValidImageFile, hashPassword, generateSalt } from '../services/security';

interface ProfileProps {
  user: User;
  onUpdate: (updatedUser: User) => void;
}

const Profile: React.FC<ProfileProps> = ({ user, onUpdate }) => {
  // General Info State
  const [fullName, setFullName] = useState(user.fullName);
  const [profilePhoto, setProfilePhoto] = useState(user.profilePhoto);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<boolean>(false);
  const [isUpdating, setIsUpdating] = useState(false);
  
  // Password State
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [passwordError, setPasswordError] = useState<string | null>(null);
  const [passwordSuccess, setPasswordSuccess] = useState<boolean>(false);
  const [isChangingPassword, setIsChangingPassword] = useState(false);

  const fileInputRef = useRef<HTMLInputElement>(null);

  const handleFileChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    setError(null);
    setSuccess(false);
    
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

  const handleSubmitGeneral = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setSuccess(false);
    setIsUpdating(true);

    if (fullName.trim().length < 2) {
      setError('Please enter a valid full name.');
      setIsUpdating(false);
      return;
    }

    try {
      const updates = { 
        fullName, 
        profilePhoto 
      };
      
      await db.updateUser(user.email, updates);
      
      const updatedUser = { ...user, ...updates };
      onUpdate(updatedUser);
      setSuccess(true);
      setTimeout(() => setSuccess(false), 3000);
    } catch (err) {
      console.error(err);
      setError('Failed to update profile. Please try again.');
    } finally {
      setIsUpdating(false);
    }
  };

  const handlePasswordChange = async (e: React.FormEvent) => {
    e.preventDefault();
    setPasswordError(null);
    setPasswordSuccess(false);
    setIsChangingPassword(true);

    if (newPassword.length < 8) {
      setPasswordError('New password must be at least 8 characters long.');
      setIsChangingPassword(false);
      return;
    }

    if (newPassword !== confirmPassword) {
      setPasswordError('New password and confirmation do not match.');
      setIsChangingPassword(false);
      return;
    }

    try {
      const auth = await db.getAuthData(user.email);
      
      let isVerified = false;
      if (user.email === 'admin@goldenbean.com' && !auth) {
        if (currentPassword === 'admin123') isVerified = true;
      } else if (auth) {
        const saltBuffer = new Uint8Array(atob(auth.salt).split('').map(c => c.charCodeAt(0)));
        const hashedAttempt = await hashPassword(currentPassword, saltBuffer);
        if (hashedAttempt === auth.hash) isVerified = true;
      }

      if (!isVerified) {
        setPasswordError('Current password is incorrect.');
        setIsChangingPassword(false);
        return;
      }

      const newSalt = generateSalt();
      const newHash = await hashPassword(newPassword, newSalt);
      const newSaltStr = btoa(String.fromCharCode(...newSalt));

      await db.updateAuthData(user.email, {
        salt: newSaltStr,
        hash: newHash
      });

      setPasswordSuccess(true);
      setCurrentPassword('');
      setNewPassword('');
      setConfirmPassword('');
      setTimeout(() => setPasswordSuccess(false), 3000);
    } catch (err) {
      console.error(err);
      setPasswordError('Failed to change password. Please try again.');
    } finally {
      setIsChangingPassword(false);
    }
  };

  return (
    <div className="max-w-2xl mx-auto mt-12 space-y-8">
      <div className="bg-white p-10 rounded-3xl shadow-xl border border-stone-100">
        <div className="mb-10">
          <h2 className="text-4xl font-serif text-stone-800">My Profile</h2>
          <p className="text-stone-500 mt-2">Manage your Green Bean account information</p>
        </div>

        {error && (
          <div className="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm animate-in fade-in slide-in-from-left-4">
            {error}
          </div>
        )}

        {success && (
          <div className="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-sm animate-in fade-in slide-in-from-left-4">
            Profile updated successfully!
          </div>
        )}

        <form onSubmit={handleSubmitGeneral} className="space-y-8">
          <div className="flex flex-col items-center">
            <div className="relative group cursor-pointer" onClick={() => fileInputRef.current?.click()}>
              <div className="w-32 h-32 rounded-full border-4 border-stone-50 overflow-hidden shadow-lg bg-stone-100 flex items-center justify-center">
                {profilePhoto ? (
                  <img src={profilePhoto} alt="Profile" className="w-full h-full object-cover" />
                ) : (
                  <span className="text-stone-400">No Image</span>
                )}
              </div>
              <div className="absolute inset-0 bg-stone-900/60 rounded-full opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center text-white transition duration-300">
                <svg className="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span className="text-[10px] font-bold uppercase tracking-wider">Change Photo</span>
              </div>
            </div>
            <input 
              type="file" 
              ref={fileInputRef}
              onChange={handleFileChange}
              accept="image/*"
              className="hidden"
            />
            <p className="text-xs text-stone-400 mt-4">Security checked: Only JPEG and PNG supported</p>
          </div>

          <div className="grid grid-cols-1 gap-6">
            <div>
              <label className="block text-sm font-semibold text-stone-700 mb-2 uppercase tracking-wide">Email (Login Identity)</label>
              <input 
                type="email" 
                disabled
                className="w-full px-5 py-3 rounded-xl border border-stone-100 bg-stone-50 text-stone-400 cursor-not-allowed font-medium"
                value={user.email}
              />
            </div>

            <div>
              <label className="block text-sm font-semibold text-stone-700 mb-2 uppercase tracking-wide">Full Name</label>
              <input 
                type="text" 
                required
                className="w-full px-5 py-3 rounded-xl border border-stone-200 focus:outline-none focus:ring-4 focus:ring-emerald-800/10 focus:border-emerald-800 transition bg-white text-stone-800 font-medium"
                placeholder="Enter your name"
                value={fullName}
                onChange={(e) => setFullName(e.target.value)}
              />
            </div>
          </div>

          <div className="pt-4">
            <button 
              type="submit" 
              disabled={isUpdating}
              className="w-full py-4 bg-emerald-800 text-white rounded-xl font-bold hover:bg-emerald-900 transition shadow-lg shadow-emerald-800/20 disabled:opacity-50 flex items-center justify-center space-x-2"
            >
              {isUpdating ? <span>Saving...</span> : <span>Update Profile</span>}
            </button>
          </div>
        </form>
      </div>

      <div className="bg-white p-10 rounded-3xl shadow-xl border border-stone-100">
        <div className="mb-8">
          <h2 className="text-2xl font-serif text-stone-800">Security & Password</h2>
          <p className="text-stone-500 mt-2 text-sm">Protect your account with a secure password</p>
        </div>

        {passwordError && (
          <div className="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm animate-in fade-in slide-in-from-left-4">
            {passwordError}
          </div>
        )}

        {passwordSuccess && (
          <div className="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-sm animate-in fade-in slide-in-from-left-4">
            Password updated successfully!
          </div>
        )}

        <form onSubmit={handlePasswordChange} className="space-y-6">
          <div>
            <label className="block text-xs font-bold text-stone-400 mb-2 uppercase tracking-widest">Current Password</label>
            <input 
              type="password" 
              required
              className="w-full px-5 py-3 rounded-xl border border-stone-200 focus:outline-none focus:ring-4 focus:ring-emerald-800/10 focus:border-emerald-800 transition bg-white text-stone-800"
              placeholder="••••••••"
              value={currentPassword}
              onChange={(e) => setCurrentPassword(e.target.value)}
            />
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-xs font-bold text-stone-400 mb-2 uppercase tracking-widest">New Password</label>
              <input 
                type="password" 
                required
                className="w-full px-5 py-3 rounded-xl border border-stone-200 focus:outline-none focus:ring-4 focus:ring-emerald-800/10 focus:border-emerald-800 transition bg-white text-stone-800"
                placeholder="Min. 8 chars"
                value={newPassword}
                onChange={(e) => setNewPassword(e.target.value)}
              />
            </div>
            <div>
              <label className="block text-xs font-bold text-stone-400 mb-2 uppercase tracking-widest">Confirm New Password</label>
              <input 
                type="password" 
                required
                className="w-full px-5 py-3 rounded-xl border border-stone-200 focus:outline-none focus:ring-4 focus:ring-emerald-800/10 focus:border-emerald-800 transition bg-white text-stone-800"
                placeholder="Repeat new password"
                value={confirmPassword}
                onChange={(e) => setConfirmPassword(e.target.value)}
              />
            </div>
          </div>

          <div className="pt-4">
            <button 
              type="submit" 
              disabled={isChangingPassword}
              className="w-full py-4 border-2 border-emerald-800 text-emerald-800 rounded-xl font-bold hover:bg-emerald-50 transition flex items-center justify-center space-x-2 disabled:opacity-50"
            >
              {isChangingPassword ? <span>Processing...</span> : <span>Change Password</span>}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default Profile;
