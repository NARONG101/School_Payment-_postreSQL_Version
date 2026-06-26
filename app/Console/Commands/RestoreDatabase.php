<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RestoreDatabase extends Command
{
    protected $signature   = 'db:restore {file? : The backup file to restore (relative to storage/backups/)}';
    protected $description = 'Restore the PostgreSQL database from a backup file in storage/backups/';

    public function handle(): int
    {
        $backupDir = storage_path('backups');

        if (! is_dir($backupDir)) {
            $this->error('Backup directory does not exist: '.$backupDir);
            return self::FAILURE;
        }

        // Get list of available backup files
        $files = glob($backupDir . DIRECTORY_SEPARATOR . "backup_*.sql");
        if (empty($files)) {
            $this->error('No backup files found in '.$backupDir);
            return self::FAILURE;
        }

        // Select backup file to restore
        $file = $this->argument('file');
        if ($file) {
            $filepath = $backupDir . DIRECTORY_SEPARATOR . $file;
            if (! file_exists($filepath)) {
                $this->error("Backup file not found: '{$file}'");
                return self::FAILURE;
            }
        } else {
            // Sort files by date (newest first)
            usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));
            $choices = array_map(fn ($f) => basename($f), $files);
            $file = $this->choice('Which backup would you like to restore?', $choices, 0);
            $filepath = $backupDir . DIRECTORY_SEPARATOR . $file;
        }

        // Confirm restore
        if (! $this->confirm("Are you sure you want to restore from " . basename($filepath) . "? This will overwrite your current database!", false)) {
            $this->info('Restore cancelled.');
            return self::SUCCESS;
        }

        $db       = config('database.connections.pgsql');
        $host     = $db['host'];
        $port     = $db['port'] ?? 5432;
        $dbname   = $db['database'];
        $username = $db['username'];
        $password = $db['password'];

        $this->info("Restoring database '{$dbname}' from " . basename($filepath) . "...");

        // Build psql restore command
        $psql = 'psql';
        $command = sprintf(
            'PGPASSWORD=%s %s --host=%s --port=%d --username=%s --dbname=%s --no-password < %s 2>&1',
            escapeshellarg($password),
            $psql,
            escapeshellarg($host),
            (int) $port,
            escapeshellarg($username),
            escapeshellarg($dbname),
            $filepath
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $this->error('Restore failed.');
            $this->line('Output: ' . implode("\n", $output));
            return self::FAILURE;
        }

        $this->info("✓ Database restored successfully from " . basename($filepath));
        return self::SUCCESS;
    }
}
