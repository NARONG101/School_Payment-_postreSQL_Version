<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:reset-database-sequences')]
#[Description('Reset PostgreSQL ID sequences for all tables to the current max ID')]
class ResetDatabaseSequences extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Resetting database sequences...');

        // Reset users sequence
        $maxUserId = DB::table('users')->max('id') ?? 0;
        DB::statement("SELECT setval(pg_get_serial_sequence('users', 'id'), ?, false)", [$maxUserId]);
        $this->info("Users sequence set to {$maxUserId}");

        // Reset students sequence
        $maxStudentId = DB::table('students')->max('id') ?? 0;
        DB::statement("SELECT setval(pg_get_serial_sequence('students', 'id'), ?, false)", [$maxStudentId]);
        $this->info("Students sequence set to {$maxStudentId}");

        // Reset payments sequence
        $maxPaymentId = DB::table('payments')->max('id') ?? 0;
        DB::statement("SELECT setval(pg_get_serial_sequence('payments', 'id'), ?, false)", [$maxPaymentId]);
        $this->info("Payments sequence set to {$maxPaymentId}");

        $this->info('Done!');
    }
}
