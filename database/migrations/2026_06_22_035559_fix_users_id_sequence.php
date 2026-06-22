<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $maxId = DB::table('users')->max('id') ?? 0;
        DB::statement("SELECT setval(pg_get_serial_sequence('users', 'id'), ?, false)", [$maxId]);
    }

    public function down(): void
    {
        //
    }
};
