-- Created by Redgate Data Modeler (https://datamodeler.redgate-platform.com)
-- Last modification date: 2026-10-27 15:05:25.67

-- tables
-- Table: accounts
CREATE TABLE accounts (
    id integer NOT NULL CONSTRAINT accounts_pk PRIMARY KEY AUTOINCREMENT,
    username text NOT NULL CHECK (length(trim(username)) > 0),
    full_name text NOT NULL CHECK (length(trim(full_name)) > 0),
    email text NOT NULL,
    password_hash text NOT NULL,
    last_login text NOT NULL DEFAULT (datetime('now')),
    initial_balance integer NOT NULL DEFAULT 0,
    created_at text NOT NULL DEFAULT (datetime('now')),
    updated_at text NOT NULL DEFAULT (datetime('now'))
);

CREATE UNIQUE INDEX ux_akun_username
ON accounts (username ASC)
;

CREATE UNIQUE INDEX ux_akun_email
ON accounts (email ASC)
;

-- Table: categories
CREATE TABLE categories (
    id integer NOT NULL CONSTRAINT categories_pk PRIMARY KEY AUTOINCREMENT,
    name text NOT NULL CHECK (length(trim(name)) > 0),
    type text NOT NULL CHECK (type IN ('income', 'expense')),
    deleted_at text,
    accounts_id integer NOT NULL,
    CONSTRAINT categories_ak_1 UNIQUE (id, accounts_id),
    CONSTRAINT categories_accounts FOREIGN KEY (accounts_id)
    REFERENCES accounts (id)
    ON DELETE CASCADE
);

CREATE UNIQUE INDEX ux_kategori_aktif
ON categories (accounts_id ASC, name ASC, type ASC)
WHERE deleted_at IS NULL;

-- Table: default_categories
CREATE TABLE default_categories (
    id integer NOT NULL CONSTRAINT default_categories_pk PRIMARY KEY AUTOINCREMENT,
    name text NOT NULL,
    type text NOT NULL CHECK (type IN ('income', 'expense')),
    CONSTRAINT default_categories_ak_1 UNIQUE (name, type)
);

-- Table: transactions
CREATE TABLE transactions (
    id integer NOT NULL CONSTRAINT transactions_pk PRIMARY KEY AUTOINCREMENT,
    type text NOT NULL CHECK (type IN ('income', 'expense')),
    amount integer NOT NULL CHECK (amount > 0),
    notes text,
    date text NOT NULL CHECK (date(date) IS date),
    time text CHECK (time(time) IS time),
    created_at text NOT NULL DEFAULT (datetime('now')),
    updated_at text NOT NULL DEFAULT (datetime('now')),
    accounts_id integer NOT NULL,
    categories_id integer NOT NULL,
    CONSTRAINT transactions_accounts FOREIGN KEY (accounts_id)
    REFERENCES accounts (id)
    ON DELETE CASCADE,
    CONSTRAINT transactions_categories FOREIGN KEY (categories_id,accounts_id)
    REFERENCES categories (id,accounts_id)
);

CREATE INDEX ix_transaksi_akun_tanggal
ON transactions (accounts_id ASC, date ASC, time ASC)
;

CREATE TRIGGER trg_accounts_seed_categories AFTER INSERT ON accounts
BEGIN
    INSERT INTO categories (accounts_id, name, type)
    SELECT NEW.id, name, type FROM default_categories;
END;

CREATE TRIGGER trg_transactions_updated_at AFTER UPDATE ON transactions
WHEN NEW.updated_at = OLD.updated_at
BEGIN
    UPDATE transactions SET updated_at = datetime('now') WHERE id = NEW.id;
END;;

-- End of file.

