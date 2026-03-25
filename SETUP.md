# Setup

## Current state
This repository contains a Phase 1 scaffold for a Laravel + Livewire based CMS.

## Next steps
1. Install PHP dependencies with `composer install`.
2. Install JS dependencies with `npm install`.
3. Copy `.env.example` to `.env`.
4. Create a SQLite database or configure MySQL/MariaDB.
5. Run `php artisan migrate`.
6. Start the app with `php artisan serve` and `npm run dev`.

## Notes
- Git repository clones are stored under `storage/repos` by default.
- Configure `GITHUB_WEBHOOK_SECRET` before using the webhook endpoint.
- Configure `GITHUB_FINE_GRAINED_TOKEN` before automating webhook registration.
