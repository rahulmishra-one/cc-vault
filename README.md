# CC Vault

CC Vault is a modular credit-card dashboard built with PHP, MySQL, HTML, CSS, and vanilla JavaScript. It is designed for deployment on Hostinger shared hosting with no framework or Composer dependency.

## Sprint 1 included

- Responsive authenticated dashboard shell
- Session-based login and logout
- Secure password hashing and prepared database queries
- Summary metrics and recent card list
- Mobile navigation
- MySQL schema with starter data
- Module-ready structure for cards, merchants, search, and recommendations

## Requirements

- PHP 8.0+ with PDO MySQL enabled
- MySQL / MariaDB
- Apache with `mod_rewrite` (optional, for friendly routing)

## Local configuration

1. Copy `config/config.example.php` to `config/config.php`.
2. Add the MySQL database credentials.
3. Import `database/database.sql` into the database.
4. Open the site in a PHP-enabled web server.

The development login is:

- Email: `admin@ccvault.local`
- Password: `password`

Change this immediately after deployment by creating a new bcrypt hash with PHP's `password_hash()` and updating the `users.password_hash` value.

## Hostinger deployment

1. Create a MySQL database and user in hPanel.
2. Import `database/database.sql` through phpMyAdmin.
3. Upload the contents of this folder to the document root for `cc.rahulmishra.org` (usually `public_html`).
4. Create `config/config.php` from the example and enter the production database credentials.
5. Visit the domain and log in. Set `APP_ENV` to `production` in `config/config.php`.

Do not upload `.env` files or commit `config/config.php` with real production credentials.

## Roadmap

See [ROADMAP.md](ROADMAP.md) for the planned releases.

## Structure

```text
cc-vault/
├── admin/              # Future administration screens
├── api/                # Future JSON endpoints
├── assets/
│   ├── css/
│   └── js/
├── config/             # Database and application configuration
├── database/           # SQL schema and seeds
├── includes/           # Shared layout and application helpers
├── pages/              # Future feature pages
├── uploads/            # Runtime uploads (not committed)
├── dashboard.php
├── index.php
├── login.php
└── logout.php
```

## GitHub

Create an empty GitHub repository named `cc-vault`, then from this project folder run:

```bash
git init
git add .
git commit -m "Initial CC Vault Sprint 1 foundation"
git branch -M main
git remote add origin https://github.com/YOUR-USERNAME/cc-vault.git
git push -u origin main
```
