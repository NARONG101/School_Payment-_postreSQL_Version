<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateOldDatabase extends Command
{
    protected $signature = 'db:migrate-old';
    protected $description = 'Copy data from old_pgsql to pgsql (new DB)';

    public function handle(): int
    {
        $this->info('Starting data migration from old DB to new DB...');

        // Disable foreign key checks temporarily
        DB::connection('pgsql')->statement('SET CONSTRAINTS ALL DEFERRED;');

        // List of tables to copy (order matters for FKs)
        $tables = [
            'users',
            'payment_types',
            'students',
            'payments',
        ];

        foreach ($tables as $table) {
            $this->info("Copying table: {$table}");

            // Get all data from old DB
            $oldData = DB::connection('old_pgsql')->table($table)->get();

            if ($oldData->isEmpty()) {
                $this->line("  No data in {$table}, skipping.");
                continue;
            }

            // Truncate the table in new DB (if any test data exists)
            DB::connection('pgsql')->table($table)->truncate();

            // Insert in chunks to avoid memory issues
            $chunkSize = 100;
            $oldData->chunk($chunkSize)->each(function ($chunk) use ($table) {
                $dataArray = $chunk->map(fn($item) => (array)$item)->toArray();
                DB::connection('pgsql')->table($table)->insert($dataArray);
            });

            $this->line("  Copied {$oldData->count()} records to {$table}");
        }

        // Re-enable foreign key checks
        DB::connection('pgsql')->statement('SET CONSTRAINTS ALL IMMEDIATE;');

        $this->info('✅ Data migration complete!');

        return self::SUCCESS;
    }
}
