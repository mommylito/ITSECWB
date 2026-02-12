
import React, { useState, useEffect } from 'react';
import { db } from '../services/db';
import { User } from '../types';

const AdminPanel: React.FC = () => {
  const [users, setUsers] = useState<User[]>([]);
  const [filter, setFilter] = useState('');

  useEffect(() => {
    db.getAllUsers().then(setUsers);
  }, []);

  const filteredUsers = users.filter(u => 
    u.fullName.toLowerCase().includes(filter.toLowerCase()) || 
    u.email.toLowerCase().includes(filter.toLowerCase())
  );

  return (
    <div className="space-y-8">
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 className="text-4xl font-serif text-stone-800">Admin Vault</h1>
          <p className="text-stone-500 mt-1">Manage baristas, customers, and system security.</p>
        </div>
        <div className="flex items-center gap-4 bg-white p-2 rounded-xl border border-stone-200 shadow-sm">
           <div className="px-3 py-1 bg-green-50 text-green-700 text-xs font-bold rounded-lg border border-green-100">SQL Database: Online</div>
           <div className="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg border border-blue-100">AI Engine: Connected</div>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white p-6 rounded-2xl border border-stone-100 shadow-sm">
          <p className="text-sm font-bold text-stone-400 uppercase tracking-widest">Total Members</p>
          <p className="text-3xl font-serif text-stone-800 mt-2">{users.length}</p>
        </div>
        <div className="bg-white p-6 rounded-2xl border border-stone-100 shadow-sm">
          <p className="text-sm font-bold text-stone-400 uppercase tracking-widest">Security Flags</p>
          <p className="text-3xl font-serif text-amber-600 mt-2">{users.filter(u => u.failedAttempts > 0).length}</p>
        </div>
        <div className="bg-white p-6 rounded-2xl border border-stone-100 shadow-sm">
          <p className="text-sm font-bold text-stone-400 uppercase tracking-widest">Active Sessions</p>
          <p className="text-3xl font-serif text-stone-800 mt-2">1</p>
        </div>
      </div>

      <div className="bg-white rounded-2xl border border-stone-100 shadow-sm overflow-hidden">
        <div className="p-6 border-b border-stone-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
          <h3 className="text-xl font-bold text-stone-800">User Management</h3>
          <div className="relative">
            <input 
              type="text" 
              placeholder="Search by name or email..." 
              className="pl-4 pr-10 py-2 rounded-lg border border-stone-200 focus:outline-none focus:ring-2 focus:ring-amber-800/20 text-sm w-full md:w-64"
              value={filter}
              onChange={(e) => setFilter(e.target.value)}
            />
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left">
            <thead className="bg-stone-50 text-stone-400 text-xs uppercase font-bold">
              <tr>
                <th className="px-6 py-4">User</th>
                <th className="px-6 py-4">Contact</th>
                <th className="px-6 py-4">Role</th>
                <th className="px-6 py-4">Security Status</th>
                <th className="px-6 py-4">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-stone-100">
              {filteredUsers.map((user) => (
                <tr key={user.id} className="hover:bg-stone-50 transition">
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-3">
                      <img src={user.profilePhoto} alt="" className="w-10 h-10 rounded-full border border-stone-200" />
                      <div className="font-semibold text-stone-800">{user.fullName}</div>
                    </div>
                  </td>
                  <td className="px-6 py-4">
                    <div className="text-sm text-stone-600">{user.email}</div>
                  </td>
                  <td className="px-6 py-4">
                    <span className={`px-2.5 py-1 rounded-full text-xs font-bold ${user.role === 'admin' ? 'bg-amber-100 text-amber-800' : 'bg-stone-100 text-stone-600'}`}>
                      {user.role}
                    </span>
                  </td>
                  <td className="px-6 py-4">
                    {user.lockoutUntil && user.lockoutUntil > Date.now() ? (
                      <span className="flex items-center gap-1.5 text-xs text-red-600 font-bold bg-red-50 px-2 py-1 rounded">
                        <span className="w-1.5 h-1.5 rounded-full bg-red-600 animate-pulse"></span>
                        LOCKED
                      </span>
                    ) : user.failedAttempts > 0 ? (
                      <span className="text-xs text-amber-600 font-bold bg-amber-50 px-2 py-1 rounded">
                        {user.failedAttempts} Failed Attempts
                      </span>
                    ) : (
                      <span className="text-xs text-green-600 font-bold bg-green-50 px-2 py-1 rounded">Secure</span>
                    )}
                  </td>
                  <td className="px-6 py-4">
                    <button className="text-sm font-semibold text-stone-400 hover:text-stone-800">View Logs</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default AdminPanel;
