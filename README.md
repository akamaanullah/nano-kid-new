# Nano Kids Wholesale — PHP app

Custom PHP 8 MVC (no Composer, no frameworks) for the Nano Kids B2B wholesale catalogue. The public site keeps the approved static design; products, quotes, applications and the admin desk are stored in MySQL / MariaDB.

## Requirements

- PHP 8.1+ with PDO MySQL, mbstring
- MySQL 5.7+ or MariaDB 10.4+
- Apache with `mod_rewrite` (XAMPP) **or** the built-in PHP server for local work

## XAMPP (Windows) setup

1. Copy this folder to `C:\xampp\htdocs\nano-kids-wholesale-php` (or any name you prefer).
2. Copy `.env.example` to `.env` and adjust if needed. Typical XAMPP defaults:

   ```
   NK_APP_ENV=local
   NK_APP_KEY=change-this-to-a-long-random-string
   NK_FORCE_HTTPS=false
   NK_DB_HOST=127.0.0.1
   NK_DB_PORT=3306
   NK_DB_NAME=nano_kids_wholesale
   NK_DB_USER=root
   NK_DB_PASS=
   ```

3. Start **Apache** and **MySQL** in the XAMPP control panel.
4. In the browser, open:

   `http://localhost/nano-kids-wholesale-php/setup.php`

   Confirm the database settings, then click **Create database and import catalogue**. This creates the database, imports `database/schema.sql` (including the 23-style catalogue) and writes `storage/setup.lock` so the installer cannot run again. If you open the storefront first, it will point you to this page until setup is complete.

5. Visit the storefront:

   `http://localhost/nano-kids-wholesale-php/`

   The root `.htaccess` rewrites into `public/`. If rewrite is off, point a virtual host document root at the `public/` folder instead.

6. Sign in to the admin desk at `/admin/login`.

### Built-in PHP server (optional)

From this folder:

```
php -S localhost:8787 -t public public/dev-router.php
```

Then open `http://localhost:8787/`.

## Default admin login

These credentials are only documented here. Change them after first sign-in on a live server.

- URL: `/admin/login` (or `/login` — admins are sent to the desk)
- Email: `admin@nanokids.pk`
- Password: `TradeAdmin#2026`

## How the site works

- Catalogue filters, sort and pagination are served from the database (and also as JSON at `/api/catalog`).
- Each product has its own **MOQ**. Price tiers must start at that MOQ. The quote form is kept in `localStorage` on the client; submission posts to the server, which re-checks MOQ and recalculates tier prices.
- Approved dealers can sign in and see their quotes and orders on `/dashboard`. Guests can still submit a quote with contact details.
- Apply, contact and private-label forms are stored in the database.
- Approving a dealership application (Admin → Applications) creates a dealer login. Set the password on the approval form and share it with the dealer.
- Uploaded product images are stored under `public/uploads/products/`.

## Project layout

```
app/            Core, controllers, models, routes
config.php      Loads .env (NK_* keys)
database/       schema.sql (schema + seed)
public/         Front controller, assets, uploads
setup.php       Guarded installer
storage/        Logs and setup.lock (keep the empty folders)
views/          Site and admin templates
```

Do not commit `.env` or `config.local.php`. Default admin details stay in this README only.
