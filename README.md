# Digital Mall

A multi-vendor online mall written in plain PHP and MySQL. Shop owners run their own stores
inside one mall, customers order from several shops with a single cart, and an admin manages the platform.

**Live demo:** https://phpproje06.freehosting.dev
Use the **Demo Customer** or **Demo Shop Manager** buttons on the login page (password `demo1234`).
Some actions, like deleting products and changing passwords, are disabled on the shared demo accounts.
To try everything, sign up with your own customer account.

## Features

**Customer**
- Browse shops and their products, with image galleries
- One cart for products from several shops. The order is split per shop at checkout
- Order history with status (Pending / Approved / Rejected)
- Account settings and password change

**Shop manager**
- Add, edit and delete products, with multiple images per product
- Categories per shop
- Approve or reject incoming orders
- Dashboard: revenue chart, sales target progress, products per category (Chart.js)
- Shop name and logo settings
- New manager accounts must be approved by the admin

**Admin**
- Mall-wide dashboard: revenue per shop, products per shop, inventory overview
- Approve or remove managers and their shops, manage users

## Security
- All queries use prepared statements (no SQL injection)
- Role-based access control, and every write checks shop ownership
- CSRF tokens on every form. State-changing actions only accept POST
- Output escaping against XSS
- Passwords hashed with `password_hash`. The session is regenerated on login, and cookies are HttpOnly and SameSite
- Image uploads are checked with `getimagesize` and limited to jpg/png/gif/webp up to 5 MB, saved with random names.
  PHP execution is disabled in `upload/`
- Prices are read from the database at checkout, never trusted from the browser
- Database credentials are kept outside the webroot. Errors are logged, not shown

## Tech
PHP 8 (mysqli), MySQL / MariaDB, HTML, CSS, vanilla JavaScript, Bootstrap 5, Chart.js. Responsive down to 320px.

## Run locally (XAMPP)
1. Copy the folder into `htdocs/`.
2. In phpMyAdmin, create a database named `products_db` and import `database/digital_mall.sql` (demo data: 4 shops, 24 products, demo accounts).
3. Open `http://localhost/<folder>/`.

Database settings come from environment variables (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`)
or from a `config.local.php` file one level above the project (see `config.php`).

## Credits
**Built by me:** the whole application (customer, shop manager and admin sides, cart and order flow,
dashboards, design) and its main security and bug fixes: prepared statements against SQL injection,
role and login checks, safe image uploads, server-side prices at checkout, per-shop order ownership,
output escaping, database config and error logging, and fixes for order status, shop logos, the revenue chart and typos.

**Built with [Claude](https://claude.ai) (AI assistant):**
- CSRF tokens, POST-only delete/logout, session hardening and current-password check
- Product images linked by `product_id` with foreign keys, safe category and manager deletion
- Responsive / mobile layout
- Public demo accounts with a write guard, and the demo notice
- Deployment: `.htaccess` rules, config template and going live on InfinityFree
