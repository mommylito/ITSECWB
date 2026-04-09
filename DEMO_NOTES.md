# The Green Bean Demo Notes

Open the app at `https://localhost/ITSECWB/`.

Regular user:  
`user@greenbean.com` / `user12345`

Admin user:  
`admin@greenbean.com` / `admin123`

Log file:  
`app.log`

## 1. SQL-based

### How to demo

- Say the app is MySQL-based and all major features use database tables.
- Show that users, menu items, reviews, orders, and order items are all stored in SQL.

### Code

- Database config: `config.php` (line 11)
- Schema and seed data: `database.sql` (line 5)
- Review table: `database.sql` (line 36)
- Orders tables: `database.sql` (line 49)

## 2. Save/display text input with at least 2 numeric inputs

### How to demo

- Log in as a regular user.
- Go to `My Reviews`.
- Create a review with:
  - text input: `Review title`
  - text input: `Review message`
  - numeric input: `Cups ordered`
  - numeric input: `Spent`
  - numeric input: `Rating (1-5)`
- Save it.
- Show that it appears on the reviews page and on the homepage under latest customer reviews.

### Code

- Review create/update logic: `reviews.php` (line 12)
- Text and numeric input fields: `reviews.php` (line 89)
- Review list display: `reviews.php` (line 132)
- Homepage review display: `index.php` (line 150)

## 3. Regular users can perform at least 3 different actions

### How to demo

- Log in as regular user.
- Show these actions:
  - create a review
  - edit a review
  - delete a review
  - place an order
- That gives you 4 regular-user actions.

### Code

- Create/edit review: `reviews.php` (line 16)
- Delete review: `reviews.php` (line 56)
- Edit/delete buttons in UI: `reviews.php` (line 140)
- Place order transaction: `index.php` (line 6)

## 4. Admin users can perform at least 3 admin-only actions

### How to demo

- Log in as admin.
- Open `Admin`.
- Show the dashboard choices first.
- Then demo:
  - Menu management: create or edit or delete a menu item
  - User account controls: lock or unlock a user
  - Order status controls: change an order status and save
- That gives more than 3 admin-only actions.

### Code

- Admin access protection: `admin.php` (line 6)
- Admin dashboard chooser: `admin.php` (line 126)
- Create/update menu: `admin.php` (line 20)
- Delete menu: `admin.php` (line 51)
- Lock user: `admin.php` (line 69)
- Unlock user: `admin.php` (line 60)
- Update order status: `admin.php` (line 83)

## 5. Logging for authentication, transactions, and administrative actions

### How to demo

- Show `app.log`.
- Then do one login, one order, and one admin action.
- Refresh the log file and show new entries were written.

### Code

- Logging function: `bootstrap.php` (line 10)
- Auth logs in login: `login.php` (line 22)
- Transaction logs in ordering: `index.php` (line 51)
- Admin logs in admin actions: `admin.php` (line 39)

## 6. Session timeout

### How to demo

- Explain timeout is set to 900 seconds = 15 minutes.
- Best quick demo: show the code and explain that after inactivity, the session is destroyed and the user must log in again.
- If you want a live demo, temporarily lower the timeout value, wait, then refresh the page.

### Code

- Timeout config: `config.php` (line 7)
- Timeout handling: `bootstrap.php` (line 84)

## 7. Error messaging with debug on/off

### How to demo

- Show that `APP_DEBUG` is currently `true`.
- Explain:
  - when true, stack trace/details are shown
  - when false, only a generic error message is shown
- For a live demo, temporarily force an error and reload once with true, then set false and reload again.

### Code

- Debug toggle: `config.php` (line 5)
- Error page behavior: `bootstrap.php` (line 23)
- Exception handler: `bootstrap.php` (line 41)

## 8. HTTPS implemented

### How to demo

- Open the site using `https://localhost/ITSECWB/`.
- Show the browser URL starts with `https://`.
- Mention this uses a self-signed certificate, which is allowed.
- You can also mention the app forces HTTPS redirects.

### Code

- HTTPS forced in config: `config.php` (line 8)
- HTTPS redirect logic: `bootstrap.php` (line 54)
