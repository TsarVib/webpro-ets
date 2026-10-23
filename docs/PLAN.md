# Build Plan

Spending tracker in Laravel, based on CDM, PDM, and SpendingTracker_PDM_create.sql.

## Database

Migrations and models:

- `accounts` (exists as Account): username, full_name, email, password_hash, last_login, initial_balance
- `categories`: name, type (income/expense), deleted_at, accounts_id
- `default_categories`: name, type
- `transactions`: type, amount, notes, date, time, accounts_id, categories_id

Rules to keep:

- Unique active category per account: (accounts_id, name, type) where deleted_at is null
- Unique default category per (name, type)
- amount is an integer greater than 0
- Category type and transaction type must match
- Transactions index on (accounts_id, date, time)
- Deleting an account deletes its categories and transactions
- New account gets default categories copied in

Seeders:

- default_categories list used for every new account

## Auth (does not count toward the 8 features)

- Register: username, full name, email, password, password confirmation
- Login with email or username, remember me option
- Logout
- Update last_login on every successful login
- Tests for all of the above (exists)

## Features

1. Dashboard
    - Current balance = initial_balance + income - expense
    - This month income total and expense total
    - Ten latest transactions with category name

2. Category list
    - Only the logged in account's categories
    - Grouped by income and expense
    - Show name and transaction count

3. Create category
    - Name, type
    - Reject duplicate active name with same type

4. Rename category
    - Same validation as create

5. Delete category (soft delete)
    - Blocked when the category still has transactions
    - Deleted category hidden from all forms and lists

6. Create transaction
    - Type, category, amount, notes, date, time
    - Category choices limited to the selected type and the account
    - Amount must be greater than 0

7. Transaction list
    - Income page and Expense page use the same table component
    - Paginated, newest first
    - Show date, time, category, notes, amount

8. Edit transaction
    - Same fields as create, prefilled
    - Changing type resets category

9. Delete transaction
    - Confirmation before delete

10. Search and filter
    - Date range, category, type, notes keyword
    - Filters kept in the URL so they survive reload

11. Monthly recap
    - Month picker
    - Income total, expense total, difference
    - Totals per category, sorted high to low

12. Profile
    - Update username, full name, email
    - Change password with current password check
    - Set initial_balance

13. Export CSV
    - Export the current filtered transaction list
    - Columns: date, time, type, category, amount, notes

## Access control

- Every category and transaction query is scoped to the logged in account
- Policies for update and delete on both models
- Unauthenticated users are redirected to login (exists)
- 404 page (exists)

## Pages

- Dashboard (exists, needs data)
- Income (exists, replace placeholder with list)
- Expense (exists, replace placeholder with list)
- Categories (list, create, edit forms)
- Transactions: create and edit forms used from Income and Expense pages
- Monthly recap
- Profile

## Shared components

- AppSidebar (exists): add Categories and Recap entries with icons
- Transaction table
- Month picker
- Filter bar
- Form fields and buttons (exist)
- Empty, loading, and error states for every list

## Tests

Feature tests per item:

- Category CRUD and duplicate/delete guards
- Transaction CRUD, amount and type validation
- Account scoping: user A cannot read or change user B's data
- Filters and recap totals
- CSV export content
- Profile update and password change

## Report and submission

- Report.PDF: CDM and PDM, feature list, screenshots with short explanation per feature, work percentage per team member
- Declaration.PDF signed by every member
- Deploy to free hosting
- Public GitHub repository link in the email
- ZIP named EF234301_WebPro_MID_<ID1>_<Name1>_<ID2>_<Name2>.ZIP with Report.PDF and Declaration.PDF
- Send before 28 October 2026, 23:59 WIB
