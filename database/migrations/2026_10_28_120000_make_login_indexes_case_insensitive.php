<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // SQLite compares text case-sensitively, so "Demo@Example.com" slipped past the plain unique indexes.
    // The app now stores both fields lowercase; indexing lower(...) makes the database refuse case variants too.
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            UPDATE accounts SET username = lower(username), email = lower(email);
            DROP INDEX IF EXISTS ux_akun_username;
            DROP INDEX IF EXISTS ux_akun_email;
            CREATE UNIQUE INDEX ux_akun_username ON accounts (lower(username));
            CREATE UNIQUE INDEX ux_akun_email ON accounts (lower(email));
            SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            DROP INDEX IF EXISTS ux_akun_email;
            DROP INDEX IF EXISTS ux_akun_username;
            CREATE UNIQUE INDEX ux_akun_username ON accounts (username);
            CREATE UNIQUE INDEX ux_akun_email ON accounts (email);
            SQL);
    }
};
