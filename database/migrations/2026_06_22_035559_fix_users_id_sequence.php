<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix users id sequence
        $maxUserId = DB::table('users')->max('id') ?? 0;
        DB::statement("SELECT setval(pg_get_serial_sequence('users', 'id'), ?, false)", [$maxUserId]);

        // Fix students id sequence
        $maxStudentId = DB::table('students')->max('id') ?? 0;
        DB::statement("SELECT setval(pg_get_serial_sequence('students', 'id'), ?, false)", [$maxStudentId]);

        // Fix payments id sequence
        $maxPaymentId = DB::table('payments')->max('id') ?? 0;
        DB::statement("SELECT setval(pg_get_serial_sequence('payments', 'id'), ?, false)", [$maxPaymentId]);
    }

    public function down(): void
    {
        //
    }
};
