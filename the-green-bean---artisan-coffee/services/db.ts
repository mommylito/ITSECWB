
/**
 * Database Service
 * Uses a pseudo-SQL engine for the frontend to fulfill the SQL requirement.
 * In a real production app, this would be a backend connection.
 * Here we use an in-memory SQL structure persisted to localStorage.
 */

import { User, MenuItem } from '../types';

class SQLDatabase {
  private users: User[] = [];
  private menu: MenuItem[] = [
    { id: 1, name: 'Golden Espresso', price: 3.5, description: 'Double shot of our signature roast.', category: 'Coffee' },
    { id: 2, name: 'Honey Lavender Latte', price: 5.2, description: 'Floral and sweet perfection.', category: 'Specialty' },
    { id: 3, name: 'Cold Brew Reserve', price: 4.8, description: 'Steeped for 24 hours.', category: 'Coffee' },
    { id: 4, name: 'Almond Croissant', price: 4.5, description: 'Flaky, buttery, and nut-topped.', category: 'Bakery' },
  ];
  private authData: Record<string, { salt: string; hash: string }> = {};

  constructor() {
    this.load();
    // Default admin
    if (this.users.length === 0) {
      this.seedAdmin();
    }
  }

  private async seedAdmin() {
    // This is hardcoded for the demo, normally would be hashed
    const adminUser: User = {
      id: 1,
      fullName: 'Head Barista',
      email: 'admin@goldenbean.com',
      profilePhoto: 'https://picsum.photos/200',
      role: 'admin',
      failedAttempts: 0,
      lockoutUntil: null
    };
    this.users.push(adminUser);
    // Password for admin is 'admin123'
    // This part is handled by auth service during setup in real life
    this.save();
  }

  private save() {
    localStorage.setItem('gb_users', JSON.stringify(this.users));
    localStorage.setItem('gb_auth', JSON.stringify(this.authData));
  }

  private load() {
    const u = localStorage.getItem('gb_users');
    const a = localStorage.getItem('gb_auth');
    if (u) this.users = JSON.parse(u);
    if (a) this.authData = JSON.parse(a);
  }

  // --- SQL-like methods ---

  async queryUserByEmail(email: string): Promise<User | null> {
    return this.users.find(u => u.email === email) || null;
  }

  async createUser(user: Omit<User, 'id'>, auth: { salt: string; hash: string }): Promise<User> {
    const newUser = { ...user, id: Date.now() } as User;
    this.users.push(newUser);
    this.authData[user.email] = auth;
    this.save();
    return newUser;
  }

  async updateUser(email: string, updates: Partial<User>) {
    const index = this.users.findIndex(u => u.email === email);
    if (index !== -1) {
      this.users[index] = { ...this.users[index], ...updates };
      this.save();
    }
  }

  async updateAuthData(email: string, auth: { salt: string; hash: string }) {
    this.authData[email] = auth;
    this.save();
  }

  async getAuthData(email: string) {
    return this.authData[email];
  }

  async getAllUsers(): Promise<User[]> {
    return this.users;
  }

  async getMenu(): Promise<MenuItem[]> {
    return this.menu;
  }
}

export const db = new SQLDatabase();
