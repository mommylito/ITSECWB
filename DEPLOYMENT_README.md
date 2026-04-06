# Deployment Notes

## Local XAMPP run

1. Place the project in `C:\xampp\htdocs\ITSECWB`.
2. Start Apache and MySQL from XAMPP.
3. Import [`database.sql`](/C:/xampp/htdocs/ITSECWB/database.sql).
4. Visit [http://localhost/ITSECWB/](http://localhost/ITSECWB/).

## Seed accounts

- Admin: `admin@greenbean.com` / `admin123`
- User: `user@greenbean.com` / `user12345`

## Security controls included

- session timeout after 15 minutes
- log file output in `logs/app.log`
- role-based admin page
- database transactions for order creation
- customer review CRUD for signed-in users
- debug-aware error page handling
- HTTPS-ready config with self-signed certificate support

## HTTPS

If Apache SSL is enabled, open the app with `https://localhost/ITSECWB/`.

To force HTTPS redirects after SSL is working, set `FORCE_HTTPS` to `true` in [`config.php`](/C:/xampp/htdocs/ITSECWB/config.php).
