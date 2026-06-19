<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            SELECT setval(
                pg_get_serial_sequence('students','id'),
                (SELECT MAX(id) FROM students)
            );
        ");
    }

    public function down(): void
    {
        //
    }
};