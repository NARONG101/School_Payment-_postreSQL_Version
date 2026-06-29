<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    protected $signature   = 'db:backup {--keep=7 : Number of backup files to keep}';
    protected $description = 'Backup the PostgreSQL database to storage/backups/';

    public function handle(): int
    {
        $backupDir = storage_path('backups');

        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $db       = config('database.connections.pgsql');
        $host     = $db['host'];
        $port     = $db['port'] ?? 5432;
        $dbname   = $db['database'];
        $username = $db['username'];
        $password = $db['password'];

        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename  = "backup_{$dbname}_{$timestamp}.sql";
        $filepath  = $backupDir . DIRECTORY_SEPARATOR . $filename;

        // Build pg_dump command
        $pgDump  = 'pg_dump';
        $command = sprintf(
            'PGPASSWORD=%s %s --host=%s --port=%d --username=%s --no-password --format=plain --no-acl --no-owner %s > %s 2>&1',
            escapeshellarg($password),
            $pgDump,
            escapeshellarg($host),
            (int) $port,
            escapeshellarg($username),
            escapeshellarg($dbname),
            $filepath
        );

        $this->info("Backing up database '{$dbname}'...");
        exec($command, $output, $returnCode);

        if ($returnCode !== 0 || ! file_exists($filepath) || filesize($filepath) < 100) {
            $this->error('Backup failed. Make sure pg_dump is in your PATH.');
            $this->line('Tip: Install PostgreSQL client tools or add pg_dump to PATH.');
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            return self::FAILURE;
        }

        $size = round(filesize($filepath) / 1024, 1);
        $this->info("✓ Backup created: {$filename} ({$size} KB)");

        // ── Rotate old backups ────────────────────────────────
        $keep  = max(1, (int) $this->option('keep'));
        $files = glob($backupDir . DIRECTORY_SEPARATOR . "backup_{$dbname}_*.sql");
        if ($files && count($files) > $keep) {
            usort($files, fn ($a, $b) => filemtime($a) <=> filemtime($b));
            $toDelete = array_slice($files, 0, count($files) - $keep);
            foreach ($toDelete as $old) {
                unlink($old);
                $this->line('Removed old backup: ' . basename($old));
            }
        }

        $remaining = count(glob($backupDir . DIRECTORY_SEPARATOR . "backup_{$dbname}_*.sql") ?: []);
        $this->info("Backup complete. {$remaining} backup(s) stored in storage/backups/");

        return self::SUCCESS;
    }
}
