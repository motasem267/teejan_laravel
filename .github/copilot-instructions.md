# Copilot Instructions — Teejan Results Dashboard

Purpose: give AI coding agents the minimal, actionable context to be productive in this Laravel + Filament app.

- **Big picture**: This is a Laravel 12 application with a Filament admin panel. Backend logic lives under `app/` (Models, Http, Observers, Policies, Providers). Routes are in `routes/web.php` and `routes/api.php`. Frontend assets are built with Vite/Tailwind (see `vite.config.js` and `package.json`).

- **Important patterns to know**:
  - `AppServiceProvider::boot()` auto-registers `App\\Observers\\ActivityObserver` for every class under `app/Models` (see `app/Providers/AppServiceProvider.php`). Avoid editing individual model boot blocks for activity logging — the observer is applied automatically.
  - Permissions & migrations: project contains many one-off permission helper scripts at repository root (e.g. `grant_admin_permission.php`, `grant_all_new_permissions.php`, `update_permission_labels.php`). These are plain PHP scripts intended to be run with the PHP CLI (e.g. `php grant_admin_permission.php`) and are the canonical place for manual permission fixes.
  - Filament resources and admin pages are under `app/Filament`. Follow existing naming and resource conventions when adding new admin UIs.
  - Policies live under `app/Policies` and are used by Laravel authorization; prefer policy methods for permission checks used by controllers and Filament resources.

- **Developer workflows / commands** (copyable):
  - Setup (fresh):
    - `composer run-script setup` — runs `composer install`, copies `.env`, runs migrations, installs npm deps and builds assets.
    - If you prefer manual steps: `composer install && cp .env.example .env && php artisan key:generate && php artisan migrate` then `npm install && npm run build`.
  - Dev (local): `composer run-script dev` — launches `php artisan serve`, queues, pail, and `vite` via `concurrently` per `composer.json`.
  - Run tests: `composer run-script test` or `php artisan test` (project has `phpunit.xml`).
  - Run individual permission scripts: `php <script>.php` from repository root (they operate on the app DB via Laravel bootstrap).

- **Files to inspect for context/examples**:
  - Service wiring & observers: `app/Providers/AppServiceProvider.php`
  - Observers: `app/Observers/ActivityObserver.php`
  - Models: `app/Models/*` — observers are registered automatically
  - Routes: `routes/web.php` and `routes/api.php`
  - Filament admin: `app/Filament/*`
  - Permission scripts: top-level files like `grant_admin_permission.php`, `grant_all_new_permissions.php`, `update_permission_labels.php`, `organize_permissions.php`.
  - Build & scripts: `composer.json`, `package.json`, `vite.config.js`, `tailwind.config.js`.

- **Project-specific conventions (do not invent alternatives)**:
  - Activity logging: rely on the global `ActivityObserver` instead of sprinkling manual event logs across models.
  - Permission fixes are implemented as curated PHP scripts in the repo root — prefer adding/updating those scripts for one-off migrations over ad-hoc DB edits.
  - Frontend changes go through Vite/Tailwind; prefer editing `resources/js` and `resources/css` and then running `npm run dev` / `npm run build`.

- **Integration & external deps**:
  - Filament (admin UI), Laravel Sanctum (auth), Vite + Tailwind for frontend.
  - PHP requirement: `^8.2` (see `composer.json`).

- **Debugging tips**:
  - Use `php artisan serve` for a quick local server; watch logs in `storage/logs/laravel.log`.
  - When touching model behavior, remember `ActivityObserver` may intercept create/update/delete events — check `app/Observers/ActivityObserver.php` first.

- **What to avoid or double-check**:
  - Do not assume manual model observers — the global registration is the source of truth.
  - Permission scripts can be sensitive: prefer adding new scripts or tests that exercise them rather than modifying DB directly.

If anything here is unclear or you want the agent to follow a stricter style (commit messages, branch naming, test coverage rules), tell me and I will iterate.
