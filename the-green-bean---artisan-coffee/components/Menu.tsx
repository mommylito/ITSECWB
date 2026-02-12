
import React, { useState, useEffect } from 'react';
import { db } from '../services/db';
import { getBaristaAdvice } from '../services/gemini';
import { User, MenuItem } from '../types';

interface MenuProps {
  user: User | null;
}

const Menu: React.FC<MenuProps> = ({ user }) => {
  const [menu, setMenu] = useState<MenuItem[]>([]);
  const [aiAdvice, setAiAdvice] = useState<string | null>(null);
  const [moodInput, setMoodInput] = useState('');
  const [isAiLoading, setIsAiLoading] = useState(false);

  useEffect(() => {
    db.getMenu().then(setMenu);
  }, []);

  const handleAskAI = async () => {
    if (!moodInput) return;
    setIsAiLoading(true);
    const advice = await getBaristaAdvice(moodInput);
    setAiAdvice(advice);
    setIsAiLoading(false);
  };

  return (
    <div className="space-y-12">
      <header className="text-center">
        <h1 className="text-5xl font-serif text-stone-800">Our Signature Menu</h1>
        <p className="mt-4 text-stone-600 max-w-2xl mx-auto">
          Crafted from ethically sourced beans and roasted in small batches to ensure the perfect profile for every cup.
        </p>
      </header>

      {/* AI Barista Component */}
      <section className="bg-emerald-50 rounded-3xl p-8 border border-emerald-100 shadow-sm">
        <div className="flex flex-col md:flex-row items-center gap-8">
          <div className="flex-1">
            <h2 className="text-2xl font-serif text-emerald-900 mb-2">AI Barista Assistant</h2>
            <p className="text-emerald-800/80 mb-6">Can't decide? Tell me how you're feeling, and I'll recommend the perfect pairing.</p>
            
            <div className="flex gap-2">
              <input 
                type="text" 
                placeholder="How are you feeling today?" 
                className="flex-1 px-4 py-3 rounded-xl border border-emerald-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition bg-white"
                value={moodInput}
                onChange={(e) => setMoodInput(e.target.value)}
              />
              <button 
                onClick={handleAskAI}
                disabled={isAiLoading || !moodInput}
                className="px-6 py-3 bg-emerald-800 text-white rounded-xl font-medium hover:bg-emerald-900 transition disabled:opacity-50 whitespace-nowrap"
              >
                {isAiLoading ? 'Thinking...' : 'Get Advice'}
              </button>
            </div>

            {aiAdvice && (
              <div className="mt-6 p-4 bg-white rounded-xl border border-emerald-200 text-stone-700 animate-in fade-in slide-in-from-top-4 duration-500">
                <span className="font-bold text-emerald-800 block mb-1">Barista's Recommendation:</span>
                "{aiAdvice}"
              </div>
            )}
          </div>
          <div className="hidden md:block w-48 h-48 rounded-2xl overflow-hidden shadow-lg border-4 border-white rotate-3">
             <img src="https://images.unsplash.com/photo-1447933601403-0c6688de566e?auto=format&fit=crop&q=80&w=400" alt="Green Coffee Beans" className="w-full h-full object-cover grayscale group-hover:grayscale-0 transition duration-500" />
          </div>
        </div>
      </section>

      {/* Grid Menu */}
      <section>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8">
          {menu.map((item) => (
            <div key={item.id} className="group bg-white p-6 rounded-2xl border border-stone-100 shadow-sm hover:shadow-md transition flex gap-6">
              <div className="w-24 h-24 bg-stone-50 rounded-xl flex-shrink-0 overflow-hidden">
                <img src={`https://picsum.photos/200/200?${item.name.replace(/\s/g, '')}&coffee`} alt={item.name} className="w-full h-full object-cover grayscale group-hover:grayscale-0 transition duration-500" />
              </div>
              <div className="flex-1 flex flex-col justify-between">
                <div>
                  <div className="flex justify-between items-start">
                    <h3 className="text-xl font-bold text-stone-800">{item.name}</h3>
                    <span className="text-emerald-800 font-bold">${item.price.toFixed(2)}</span>
                  </div>
                  <p className="text-sm text-stone-500 mt-2">{item.description}</p>
                </div>
                <div className="mt-4 flex items-center justify-between">
                  <span className="text-xs uppercase tracking-widest text-stone-400 font-bold">{item.category}</span>
                  <button className="text-sm font-semibold text-emerald-800 hover:text-emerald-900">Add to Order +</button>
                </div>
              </div>
            </div>
          ))}
        </div>
      </section>

      <section className="bg-emerald-900 text-stone-100 rounded-3xl p-12 text-center overflow-hidden relative">
        <div className="relative z-10">
          <h2 className="text-4xl font-serif mb-4">Green Rewards</h2>
          <p className="text-emerald-200 max-w-lg mx-auto mb-8">Every cup counts. Earn stars on every order and unlock secret seasonal blends.</p>
          <button className="px-8 py-3 bg-white text-emerald-900 rounded-full font-bold hover:bg-stone-200 transition">View My Rewards</button>
        </div>
        <div className="absolute top-0 right-0 w-64 h-64 bg-emerald-800/10 rounded-full -mr-20 -mt-20 blur-3xl"></div>
        <div className="absolute bottom-0 left-0 w-64 h-64 bg-emerald-500/10 rounded-full -ml-20 -mb-20 blur-3xl"></div>
      </section>
    </div>
  );
};

export default Menu;
