# StayEasy — vulnerable booking demo

> **Deliberately insecure. Never deploy this, expose it to a network, or reuse its code.**

A small Laravel 10 hotel-booking app used to demonstrate Vuln Scanner. The code
*looks* careful — form requests, policies, query bindings, allow-lists, HMAC
signatures — but nearly every control has a subtle flaw. It also pins
dependencies with published advisories, and ships an insecure Dockerfile,
Kubernetes manifest and GitHub Actions workflow.

What's planted, and which part of Vuln Scanner should find it, is in
[DEMO.md](DEMO.md). Don't show DEMO.md before the scan.

## Run locally

Requires PHP 8.1 (mPDF 8.1 doesn't install on 8.2+) and Composer.

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
# set DB_CONNECTION=sqlite and DB_DATABASE=<absolute path to database.sqlite> in .env
php artisan migrate --seed
php artisan serve
```

Seeded users (password `password`): `alice@stayeasy.test`, `bob@stayeasy.test`,
`admin@stayeasy.test`. Bookings `BK-000001` (Alice) and `BK-000002` (Bob).

`composer.json` sets `audit.block-insecure: false`: Composer 2.9+ otherwise
refuses to install the vulnerable versions this demo needs.

The `sk_live_…` value in `config/services.php` is fake, but shaped like a
Stripe key on purpose. GitHub push protection will block a push containing it
unless you allow it for this repository.
