<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Login looks accounts up by username or email, so the database must keep both unique.
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE UNIQUE INDEX ux_akun_username ON accounts (username);
            CREATE UNIQUE INDEX ux_akun_email ON accounts (email);
            SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            DROP INDEX IF EXISTS ux_akun_email;
            DROP INDEX IF EXISTS ux_akun_username;
            SQL);
    }
};
