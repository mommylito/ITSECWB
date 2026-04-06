# The Green Bean

SQL-based coffee shop website for Milestone 2.

## Implemented requirements

1. SQL-based using MySQL and PDO prepared statements.
2. Saves and displays text input through `cafe_reviews`.
3. Includes at least 2 numeric inputs:
   - `cups_count`
   - `spending_amount`
   - `sweetness_level`
4. Regular-user actions:
   - create customer review
   - edit own customer review
   - delete own customer review
   - place order
5. Admin-only actions:
   - create menu item
   - update menu item
   - delete menu item
   - lock users
   - unlock locked users
   - update order status
6. Logging to `logs/app.log` for:
   - authentication
   - transactions
   - administrative actions
7. Session timeout implemented at 15 minutes of inactivity.
8. Error handling:
   - detailed stack trace when `APP_DEBUG` is `true`
   - generic message when `APP_DEBUG` is `false`
9. HTTPS implemented through XAMPP SSL and forced in `config.php`.

## Default accounts

- Admin: `admin@greenbean.com` / `admin123`
- User: `user@greenbean.com` / `user12345`

## Setup

1. Copy the project into `C:\xampp\htdocs\ITSECWB`.
2. Start Apache and MySQL in XAMPP.
3. Import [`database.sql`](/C:/xampp/htdocs/ITSECWB/database.sql) in phpMyAdmin or MySQL Workbench.
4. Open [https://localhost/ITSECWB/](https://localhost/ITSECWB/).

## Files

- [`bootstrap.php`](/C:/xampp/htdocs/ITSECWB/bootstrap.php): shared session, logging, CSRF, error handling, layout helpers
- [`index.php`](/C:/xampp/htdocs/ITSECWB/index.php): coffee shop homepage with menu, ordering, and customer reviews
- [`reviews.php`](/C:/xampp/htdocs/ITSECWB/reviews.php): regular-user CRUD for customer reviews
- [`admin.php`](/C:/xampp/htdocs/ITSECWB/admin.php): admin-only actions
- [`config.php`](/C:/xampp/htdocs/ITSECWB/config.php): debug, session timeout, HTTPS, database config
