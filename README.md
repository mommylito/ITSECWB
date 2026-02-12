# The Green Bean - Deployment Guide

A secure coffee shop web application built with PHP, MySQL, and Tailwind CSS.

---

## 📋 Table of Contents

1. [Prerequisites](#prerequisites)
2. [Installation](#installation)
3. [Database Setup](#database-setup)
4. [Configuration](#configuration)
5. [Running the Application](#running-the-application)
6. [Testing](#testing)
7. [Troubleshooting](#troubleshooting)
8. [Project Structure](#project-structure)
9. [Security Features](#security-features)
10. [Team Collaboration](#team-collaboration)

---

## 🔧 Prerequisites

Before you begin, ensure you have the following installed:

- **XAMPP** (v8.0 or higher)
  - Includes Apache, MySQL, and PHP
  - Download: https://www.apachefriends.org/
- **MySQL Workbench** (Optional but recommended, use phpmyadmin as alternative)
  - Download: https://dev.mysql.com/downloads/workbench/
- **Web Browser** (Chrome, Firefox, Edge, etc.)
- **Text Editor** (VS Code, Sublime Text, etc.)

---

## 📦 Installation

### Step 1: Download and Install XAMPP

1. Download XAMPP from https://www.apachefriends.org/
2. Run the installer
3. Install to default location: `C:\xampp` (Windows) or `/Applications/XAMPP` (Mac)
4. During installation, make sure **Apache** and **MySQL** are selected
5. Complete the installation

### Step 2: Get Project Files

1. Extract the project files or clone the repository
2. Copy all project files to XAMPP's web directory:
   - **Windows:** `C:\xampp\htdocs\green-bean\`
   - **Mac:** `/Applications/XAMPP/htdocs/green-bean/`
   - **Linux:** `/opt/lampp/htdocs/green-bean/`

Your folder structure should look like this:
```
htdocs/
└── green-bean/
    ├── admin.php
    ├── database.sql
    ├── db_connect.php
    ├── index.php
    ├── login.php
    ├── logout.php
    ├── profile.php
    ├── register.php
    └── README.md
```

---

## 🗄️ Database Setup

### Method 1: Using MySQL Workbench (Recommended)

#### Step 1: Start MySQL Service

1. Open **XAMPP Control Panel**
2. Click **Start** next to **MySQL**
3. Wait until it shows "Running" with green highlight

#### Step 2: Create MySQL Workbench Connection

1. Open **MySQL Workbench**
2. Click the **+** icon next to "MySQL Connections"
3. Configure connection:
   - **Connection Name:** `Green Bean Local`
   - **Connection Method:** `Standard (TCP/IP)`
   - **Hostname:** `127.0.0.1` (or `localhost`)
   - **Port:** `3306`
   - **Username:** `root`
   - **Password:** Leave empty (click "Store in Keychain/Vault" and leave blank)
4. Click **Test Connection**
5. If successful, click **OK**

#### Step 3: Run Database Script

1. Double-click your connection to open it
2. Click **File** → **Open SQL Script**
3. Navigate to `green-bean/database.sql`
4. Click **Open**
5. Click the **⚡ Execute** button (lightning bolt icon)
6. Verify in the output:
   - `green_bean` database created
   - `users` table created
   - `categories` table created
   - `menu_items` table created
   - `orders` table created
   - `order_items` table created
   - 3 category items inserted
   - 4 menu items inserted
   - 1 user inserted

#### Step 4: Verify Database

Run this query to verify:
```sql
USE green_bean;
SHOW TABLES;
SELECT * FROM menu_items;
SELECT email, role FROM users;
```

You should see:
- 2 tables: `users`, `menu_items`
- 4 menu items
- 1 admin user (admin@greenbean.com)

### Method 2: Using phpMyAdmin (Alternative)

1. Start Apache and MySQL in XAMPP Control Panel
2. Open browser and go to: `http://localhost/phpmyadmin`
3. Click **New** in the left sidebar
4. Database name: `green_bean`
5. Click **Create**
6. Click **Import** tab
7. Click **Choose File** and select `database.sql`
8. Scroll down and click **Go**
9. Verify tables were created successfully

### Method 3: Using Command Line

1. Open Command Prompt/Terminal
2. Navigate to MySQL bin directory:
   ```bash
   # Windows
   cd C:\xampp\mysql\bin
   
   # Mac/Linux
   cd /Applications/XAMPP/xamppfiles/bin
   ```
3. Login to MySQL:
   ```bash
   mysql -u root -p
   ```
   Press Enter (password is empty)
4. Run the script:
   ```sql
   source C:/xampp/htdocs/green-bean/database.sql
   ```
   (Adjust path as needed)

---

## ⚙️ Configuration

### Step 1: Verify Database Connection Settings

Open `db_connect.php` and verify:

```php
<?php
$host = 'localhost';     // or '127.0.0.1'
$db   = 'green_bean';    // database name
$user = 'root';          // XAMPP default user
$pass = '';              // XAMPP default password (empty)
$charset = 'utf8mb4';
```

**Important:** 
- Default XAMPP MySQL password is **empty**
- If you changed MySQL password, update `$pass` accordingly

### Step 2: Check PHP Version

1. Open XAMPP Control Panel
2. Click **Shell**
3. Type: `php -v`
4. Ensure PHP version is **7.4 or higher**

### Step 3: Verify Apache Configuration

1. Make sure Apache is using port **80** (default)
2. If port 80 is in use (e.g., by Skype, IIS):
   - Click **Config** next to Apache
   - Select **httpd.conf**
   - Find `Listen 80` and change to `Listen 8080`
   - Save and restart Apache
   - Access app at `http://localhost:8080/green-bean/`

---

## 🚀 Running the Application

### Step 1: Start Services

1. Open **XAMPP Control Panel**
2. Click **Start** next to **Apache**
3. Click **Start** next to **MySQL**
4. Both should show "Running" with green background

### Step 2: Access the Application

Open your web browser and navigate to:
```
http://localhost/green-bean/
```

Or if using port 8080:
```
http://localhost:8080/green-bean/
```

### Step 3: First Login

Use the default admin account:
- **Email:** `admin@greenbean.com`
- **Password:** `admin123`

**⚠️ IMPORTANT:** Change this password in production!

---

## 🧪 Testing

### Test 1: Homepage

1. Go to `http://localhost/green-bean/`
2. Verify you can see:
   - Navigation bar with "The Green Bean" logo
   - "Login" and "Join Now" buttons
   - Menu items (4 coffee/bakery items)
   - Item images, prices, and descriptions

### Test 2: User Registration

1. Click **Join Now**
2. Fill in the form:
   - Full Name: Your Name
   - Email: your.email@example.com
   - Password: testpass123 (minimum 8 characters)
3. Click **Join Community**
4. Should redirect to login page

### Test 3: User Login

1. Go to login page
2. Enter your registered credentials
3. Click **Sign In**
4. Should redirect to homepage
5. Verify navigation shows "Profile" and "Logout"

### Test 4: Admin Login

1. Logout if logged in
2. Login with admin credentials:
   - Email: admin@greenbean.com
   - Password: admin123
3. Verify "Admin" link appears in navigation
4. Click **Admin** → should see user list with security status

### Test 5: Profile Update

1. Login as any user
2. Click **Profile**
3. Update full name
4. (Optional) Upload a profile photo (JPG or PNG only)
5. Click **Save Changes**
6. Verify success message appears

### Test 6: Security Features

**Test Account Lockout:**
1. Logout
2. Try to login with wrong password 5 times
3. Account should be locked for 15 minutes
4. Verify error message shows lockout

**Test File Upload Security:**
1. Login and go to Profile
2. Try to upload a non-image file (e.g., .txt, .pdf)
3. Should show error: "Invalid file type"

---

## 🐛 Troubleshooting

### Problem: "Database connection failed"

**Solutions:**
1. Verify MySQL is running in XAMPP Control Panel
2. Check `db_connect.php` credentials
3. Verify database `green_bean` exists:
   ```bash
   mysql -u root -p
   SHOW DATABASES;
   ```
4. Check if port 3306 is blocked by firewall

### Problem: "Page not found" (404 Error)

**Solutions:**
1. Verify files are in correct directory: `htdocs/green-bean/`
2. Check Apache is running
3. Try accessing: `http://localhost/` first
4. Clear browser cache

### Problem: Apache won't start

**Solutions:**
1. **Port 80 conflict:**
   - Check if Skype, IIS, or other program uses port 80
   - Change Apache port to 8080 (see Configuration section)
2. **Firewall blocking:**
   - Allow Apache in Windows Firewall
3. **Previous instance running:**
   - Open Task Manager
   - End all `httpd.exe` processes
   - Restart Apache

### Problem: MySQL won't start

**Solutions:**
1. **Port 3306 conflict:**
   - Check if another MySQL instance is running
   - Change MySQL port in XAMPP config
2. **MySQL service stuck:**
   - Open Command Prompt as Administrator
   - Run: `net stop mysql`
   - Then start MySQL in XAMPP
3. **Corrupt data:**
   - Backup `C:\xampp\mysql\data\`
   - Reinstall XAMPP

### Problem: MySQL Workbench crashes when connecting

**Solutions:**
1. Use `127.0.0.1` instead of `localhost`
2. Update to latest MySQL Workbench version
3. Use phpMyAdmin as alternative: `http://localhost/phpmyadmin`
4. Try HeidiSQL: https://www.heidisql.com/

### Problem: Images not displaying

**Solutions:**
1. Check internet connection (uses external CDN for placeholder images)
2. Verify Tailwind CSS CDN is loading
3. Check browser console for errors (F12)

### Problem: Session errors

**Solutions:**
1. Clear browser cookies
2. Check `php.ini` session settings
3. Verify session directory is writable:
   - Windows: `C:\xampp\tmp\`

---

## 📁 Project Structure

```
green-bean/
│
├── index.php           # Homepage with menu items
├── login.php           # User login with brute-force protection
├── register.php        # New user registration
├── profile.php         # User profile management
├── admin.php           # Admin dashboard (role-based access)
├── logout.php          # Session destruction
│
├── db_connect.php      # Database connection configuration
├── database.sql        # Database schema and seed data
│
├── package.json        # Node dependencies (if using Vite)
├── tsconfig.json       # TypeScript config (if applicable)
└── README.md           # This file
```

### File Descriptions

| File | Purpose |
|------|---------|
| `index.php` | Main landing page displaying coffee menu |
| `login.php` | Authentication with account lockout after 5 failed attempts |
| `register.php` | User registration with email validation |
| `profile.php` | Profile editing and photo upload |
| `admin.php` | Admin panel to view all users and security status |
| `logout.php` | Destroys session and logs out user |
| `db_connect.php` | PDO database connection with error handling |
| `database.sql` | Creates database, tables, and inserts seed data |

---

## 🔒 Security Features

This application implements several security best practices:

### 1. **Password Security**
- Passwords hashed with `bcrypt` (PASSWORD_BCRYPT)
- Minimum 8 character requirement
- Never stored in plain text

### 2. **Brute Force Protection**
- Account locks after 5 failed login attempts
- 15-minute lockout period
- Remaining attempts counter

### 3. **SQL Injection Prevention**
- All queries use prepared statements
- PDO with parameterized queries
- No direct SQL string concatenation

### 4. **File Upload Security**
- MIME type validation (only JPEG/PNG)
- File size limits
- Base64 encoding for database storage
- Server-side validation

### 5. **Session Security**
- Session-based authentication
- Role-based access control (admin/user)
- Automatic session destruction on logout

### 6. **XSS Prevention**
- All user input escaped with `htmlspecialchars()`
- Output encoding for display

### 7. **Access Control**
- Admin routes protected with role checks
- Unauthenticated users redirected to login

---

## 👥 Team Collaboration

### For Team Members Setting Up Locally

Each team member should:

1. **Install XAMPP** on their local machine
2. **Copy project files** to their `htdocs/green-bean/` directory
3. **Run `database.sql`** to create identical database
4. **Test the application** to ensure everything works

### Syncing Changes

**Option 1: Manual File Sharing**
- Share updated PHP files via cloud storage
- Each member replaces files in their local directory
- Re-run database scripts if schema changes

**Option 2: Version Control (Recommended)**
```bash
# Initialize git repository
git init

# Create .gitignore
echo "vendor/" > .gitignore
echo "node_modules/" >> .gitignore
echo ".env" >> .gitignore

# Make initial commit
git add .
git commit -m "Initial commit"

# Push to GitHub/GitLab
git remote add origin <repository-url>
git push -u origin main
```

### Database Schema Changes

If you modify the database:
1. Export updated schema:
   ```sql
   mysqldump -u root green_bean > database_v2.sql
   ```
2. Share with team
3. Team members can drop and recreate:
   ```sql
   DROP DATABASE IF EXISTS green_bean;
   SOURCE database_v2.sql;
   ```

---

## 📝 Default Credentials

### Admin Account
- **Email:** `admin@greenbean.com`
- **Password:** `admin123`
- **Role:** admin

### Test User Account (Create via registration)
- Register at: `http://localhost/green-bean/register.php`

---

## 🔄 Updating the Application

### Adding New Menu Items

1. Login to phpMyAdmin: `http://localhost/phpmyadmin`
2. Select `green_bean` database
3. Click `menu_items` table
4. Click **Insert** tab
5. Add item data:
   - name: Item name
   - price: Decimal (e.g., 4.99)
   - description: Item description
   - category: Coffee/Bakery/Specialty
6. Click **Go**

Or via SQL:
```sql
INSERT INTO menu_items (name, price, description, category) 
VALUES ('New Coffee', 5.99, 'Description here', 'Coffee');
```

### Creating New Admin Users

```sql
-- Generate password hash first
-- In PHP, run: password_hash('yourpassword', PASSWORD_BCRYPT);

INSERT INTO users (full_name, email, password_hash, role) 
VALUES ('New Admin', 'newadmin@example.com', '$2y$10$hash...', 'admin');
```

---

## 🌐 Deployment to Production

**⚠️ Before deploying to live server:**

1. **Change database credentials** in `db_connect.php`
2. **Update admin password** (remove seed admin or change password)
3. **Enable HTTPS** (SSL certificate required)
4. **Set proper file permissions** (644 for files, 755 for directories)
5. **Disable error display:**
   ```php
   ini_set('display_errors', 0);
   error_reporting(0);
   ```
6. **Add `.htaccess` security rules**
7. **Regular backups** of database
8. **Update Tailwind CDN** to local build for production

---

## 📞 Support

If you encounter issues:

1. Check the **Troubleshooting** section above
2. Review XAMPP error logs:
   - Apache: `C:\xampp\apache\logs\error.log`
   - MySQL: `C:\xampp\mysql\data\mysql_error.log`
3. Verify PHP errors: Enable in `php.ini`:
   ```ini
   display_errors = On
   error_reporting = E_ALL
   ```
4. Contact your team lead or instructor

---

## 📄 License

This project is for educational purposes.

---

## ✅ Quick Start Checklist

- [ ] XAMPP installed
- [ ] Apache running (green in XAMPP)
- [ ] MySQL running (green in XAMPP)
- [ ] Project files in `htdocs/green-bean/`
- [ ] Database created via `database.sql`
- [ ] Can access `http://localhost/green-bean/`
- [ ] Can login with admin credentials
- [ ] Can register new user
- [ ] Can update profile

---

**Last Updated:** February 2026  
**Version:** 1.0.0
