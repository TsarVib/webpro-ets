# Spending Tracker

A personal finance web app for recording income and expenses, organising them into categories, and reviewing monthly recaps with charts and exportable reports.

Built for the **EF234301 Web Programming (D) midterm** (2026/2027-1): a CRUD website in Laravel with at least eight features beyond authentication and a database schema with foreign keys.

**Live demo:** _to be added after deployment_

**Demo login:** username `demo` (or `demo@example.com`), password `demo1234`. It comes with three months of sample income and expenses. This is a shared public account, so don't enter anything private.

## Team

| NRP        | Name                             |
| ---------- | -------------------------------- |
| 5025251020 | Firsto Al Kautsar Jagad Kurniaji |
| 5025251098 | Wistara Banyu Kayana             |
| 5025251108 | Dimas Maulana Putra              |

## Tech stack

| Layer            | Technology                                                                     |
| ---------------- | ------------------------------------------------------------------------------ |
| Backend          | Laravel 13 (PHP 8.3+)                                                          |
| Frontend         | Svelte 5 + Inertia.js 3, Tailwind CSS 4                                        |
| Database         | SQLite (schema in raw SQL with `CHECK` constraints, foreign keys and triggers) |
| PDF export       | barryvdh/laravel-dompdf                                                        |
| Type-safe routes | Laravel Wayfinder                                                              |
| Quality          | Pest, PHPStan (level 7, Larastan), Pint, svelte-check, Vite+ lint/format       |

## Features

**Authentication** (not counted toward the eight features)

- Register with username, full name, email and password
- Log in with email **or** username, with a "remember me" option
- Log out; the last login time is recorded on every sign-in

**Application features**

1. **Dashboard**: current balance (initial balance + income − expense), this month's income and expense, and the latest transactions with filters
2. **Category list**: the account's categories, grouped by income and expense
3. **Create category**: duplicate active names of the same type are rejected
4. **Rename category**: same validation; a category's type cannot change while it has transactions
5. **Delete category**: soft delete, blocked while the category still has transactions
6. **Create transaction**: category, amount, notes, date and time; the type comes from the Income or Expense page, and only categories of that type are offered
7. **Transaction lists**: separate Income and Expense pages, paginated, newest first
8. **Edit transaction**: the same form, prefilled
9. **Delete transaction**: with confirmation
10. **Search and filter**: by date range, category, type and notes keyword, kept in the URL so they survive a reload
11. **Monthly recap**: single month, month range or full year; income, expense and net totals; per-category totals with bar and wheel charts
12. **Profile settings**: update username, full name, email and initial balance; change the password with a current-password check; choose a theme accent and light/dark mode
13. **Export**: CSV of the filtered income/expense lists, and the recap as CSV, a printable HTML report or a PDF

Income is always shown in green with a `+` sign and expense in red with a `−` sign. These colours don't depend on the chosen theme accent.

**Access control:** every category and transaction query is scoped to the logged-in account. A policy (transactions) and ownership checks (categories) guard updates and deletes, and guests are redirected to the login page.

## Database design

The conceptual and physical data models are in [`docs/`](docs):

- Conceptual data model: [`docs/CDM.png`](docs/CDM.png)
- Physical data model: [`docs/PDM.jpeg`](docs/PDM.jpeg)
- Generated DDL: [`docs/SpendingTracker_PDM_create.sql`](docs/SpendingTracker_PDM_create.sql)

| Table                | Purpose                                                                       |
| -------------------- | ----------------------------------------------------------------------------- |
| `accounts`           | Users: username, full name, email, password hash, last login, initial balance |
| `default_categories` | Template categories copied to every new account                               |
| `categories`         | Per-account income/expense categories (soft deleted)                          |
| `transactions`       | Income and expense entries: type, amount, notes, date, time                   |

Integrity rules are enforced in the database itself, not only in the app:

- **Foreign keys:** `categories.accounts_id → accounts.id` and `transactions.accounts_id → accounts.id`, both `ON DELETE CASCADE`. There is also a composite `transactions (categories_id, accounts_id) → categories (id, accounts_id)`, so a transaction can only use a category owned by the same account.
- **Checks:** `amount > 0`, `type IN ('income', 'expense')`, valid dates and times, and non-blank names.
- **Unique logins:** `accounts.username` and `accounts.email` each have a unique index.
- **Unique active category** per account, name and type (a partial index on `deleted_at IS NULL`).
- **Triggers:** a new account gets the default categories, and `transactions.updated_at` is maintained automatically.

## Running locally

Requirements: PHP 8.3+ with the `pdo_sqlite`, `mbstring`, `xml`, `dom` and `gd` extensions, Composer, and Node.js 22+.

```bash
git clone https://github.com/TsarVib/webpro-ets.git
cd webpro-ets
composer setup   # install dependencies, create .env, generate key, migrate + seed (default categories, demo account), build assets
composer dev     # start the app
```

Open http://localhost:8000, register an account and log in. New accounts start with the default income and expense categories.

## Testing

```bash
php artisan test      # feature tests only
composer ci:check     # everything CI runs: lint, format, svelte-check, Pint, PHPStan and tests
```

The feature suite covers authentication, the dashboard, categories, transactions, filters, pagination, CSV/HTML/PDF exports, the recap, settings, account isolation (one account cannot read or change another's data), and the database constraints and cascades.

GitHub Actions runs `composer ci:check` on every push to `main` and on every pull request.

## Project structure

```text
app/Http/Controllers/   Auth, Dashboard, Category, Transaction, Recap, Settings
app/Models/             Account, Category, Transaction
app/Policies/           TransactionPolicy (update/delete ownership)
database/migrations/    SQLite schema, constraints and triggers
database/seeders/       Default categories
resources/js/pages/     Svelte pages (Home, Income, Expense, Categories, Recap, Settings, auth)
resources/views/recap/  Printable / PDF recap report
tests/Feature/          Pest feature tests
docs/                   Assignment brief, data models, build plan
```
