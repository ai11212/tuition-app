## How to help in this repository

This is a small Laravel 12 application (PHP 8.2+) for managing a tuition centre: students, staff, attendance and payments. Use these concise, repository-specific rules when editing or suggesting code.

- Project entry points & framework
  - The app is a Laravel skeleton. Application bootstrap is in `bootstrap/app.php`. Routes are defined in `routes/web.php` and map to controllers in `app/Http/Controllers`.
  - Models are in `app/Models` (e.g., `Student.php`, `Invoice.php`, `PaymentTransaction.php`). Use Eloquent patterns already present (create(), query(), where()) when adding DB access.

- Conventions & patterns to follow
  - Controllers use request validation with `$request->validate([...])` and return Blade views via `view(...)` or redirects via `redirect()->route(...)`. Mirror this style when adding features.
  - The students admission flow is a session-driven multi-step wizard (see `StudentController::next`, `confirm`, `store`). Preserve session keys like `admission` and its shape when interacting with that flow.
  - Defensive column checks: some code uses `Schema::hasColumn(...)` before writing optional columns (see `PaymentController` handling of `paid_at`). If introducing migrations that add optional columns, keep similar checks for backward compatibility.
  - CSV/print outputs are implemented by streaming headers and writing to `php://output` (see `PaymentController::exportCsv`). Follow that pattern for similar exports.

- Tests, dev and build commands
  - Composer scripts: run `composer test` to execute PHP tests; `composer run-script dev` will run a dev stack (artisan serve, queue:listen, pail, and `npm run dev`). See `composer.json` scripts.
  - Frontend uses Vite and Tailwind (see `package.json`). Use `npm run dev` for development and `npm run build` for production assets.

- Database & migrations
  - Migrations live in `database/migrations`. The code sometimes expects a `payment_transactions.paid_at` column to be optional; check migrations before relying on new fields.
  - The project may use SQLite during initial setup (composer `post-create-project-cmd` touches `database/database.sqlite`). When editing migrations, run `php artisan migrate` (or `php artisan migrate:fresh --seed` for local dev).

- Errors, logging and environment
  - Authentication uses Laravel's `Auth` facade (`AuthController`). Routes requiring auth are grouped under the `auth` middleware in `routes/web.php`.
  - Keep controller methods small and prefer using models/queries rather than raw DB logic spread across controllers.

- Files and places to look for examples
  - Session-driven wizard: `app/Http/Controllers/StudentController.php` (uses session `admission`).
  - Defensive schema checks and exports: `app/Http/Controllers/PaymentController.php`.
  - Routing patterns and resource controllers: `routes/web.php` (uses `Route::resource` for staff/books).

- When to modify tests and migrations
  - If you change the DB schema add/update migrations and update any code that uses `Schema::hasColumn` checks. Add PHPUnit tests under `tests/Feature` for controller flows that manipulate sessions or files.

- Safety and compatibility
  - Target PHP 8.2+ and Laravel 12. Keep code style consistent with the project: minimal, imperative controller methods, using facades and helper functions (e.g., `now()`, `view()`, `redirect()`).

If anything here is unclear or you need additional conventions (coding style, preferred helper functions, or test setup instructions), tell me which area to expand and I will update this file.
