<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE TABLE accounts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL CHECK (length(trim(username)) > 0),
                full_name TEXT NOT NULL CHECK (length(trim(full_name)) > 0),
                email TEXT NOT NULL,
                password_hash TEXT NOT NULL,
                last_login TEXT NOT NULL DEFAULT (datetime('now')),
                initial_balance INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL DEFAULT (datetime('now')),
                updated_at TEXT NOT NULL DEFAULT (datetime('now'))
            );

            CREATE TABLE default_categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                type TEXT NOT NULL CHECK (type IN ('income', 'expense')),
                UNIQUE (name, type)
            );

            CREATE TABLE categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL CHECK (length(trim(name)) > 0),
                type TEXT NOT NULL CHECK (type IN ('income', 'expense')),
                deleted_at TEXT,
                accounts_id INTEGER NOT NULL,
                UNIQUE (id, accounts_id),
                FOREIGN KEY (accounts_id) REFERENCES accounts (id) ON DELETE CASCADE
            );

            CREATE UNIQUE INDEX ux_kategori_aktif
                ON categories (accounts_id, name, type)
                WHERE deleted_at IS NULL;

            CREATE TABLE transactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                type TEXT NOT NULL CHECK (type IN ('income', 'expense')),
                amount INTEGER NOT NULL CHECK (amount > 0),
                notes TEXT,
                date TEXT NOT NULL CHECK (date(date) IS date),
                time TEXT CHECK (time IS NULL OR time(time) IS time),
                created_at TEXT NOT NULL DEFAULT (datetime('now')),
                updated_at TEXT NOT NULL DEFAULT (datetime('now')),
                accounts_id INTEGER NOT NULL,
                categories_id INTEGER NOT NULL,
                FOREIGN KEY (accounts_id) REFERENCES accounts (id) ON DELETE CASCADE,
                FOREIGN KEY (categories_id, accounts_id)
                    REFERENCES categories (id, accounts_id)
            );

            CREATE INDEX ix_transaksi_akun_tanggal
                ON transactions (accounts_id, date, time);

            CREATE TRIGGER trg_accounts_seed_categories
            AFTER INSERT ON accounts
            BEGIN
                INSERT INTO categories (accounts_id, name, type)
                SELECT NEW.id, name, type FROM default_categories;
            END;

            CREATE TRIGGER trg_transactions_updated_at
            AFTER UPDATE ON transactions
            WHEN NEW.updated_at = OLD.updated_at
            BEGIN
                UPDATE transactions
                SET updated_at = datetime('now')
                WHERE id = NEW.id;
            END;
            SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            DROP TRIGGER IF EXISTS trg_transactions_updated_at;
            DROP TRIGGER IF EXISTS trg_accounts_seed_categories;
            DROP INDEX IF EXISTS ux_kategori_aktif;
            DROP INDEX IF EXISTS ix_transaksi_akun_tanggal;
            DROP TABLE IF EXISTS transactions;
            DROP TABLE IF EXISTS categories;
            DROP TABLE IF EXISTS default_categories;
            DROP TABLE IF EXISTS accounts;
            SQL);
    }
};
