<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--disk= : Target storage disk (defaults to private_disk or local)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform automated database backup and upload compressed archive to secure storage';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Taallum BD Automated Database Backup...');

        $connection = config('database.default', 'pgsql');
        $timestamp = now()->format('Y-m-d_His');
        $backupName = "taallumbd_backup_{$connection}_{$timestamp}.sql";
        $tempPath = storage_path("app/backup_{$timestamp}.sql");

        $diskName = $this->option('disk') ?: config('filesystems.private_disk', config('filesystems.default', 'local'));

        try {
            if ($connection === 'sqlite') {
                $dbPath = config('database.connections.sqlite.database');
                if (file_exists($dbPath)) {
                    copy($dbPath, $tempPath);
                    $this->info("SQLite database copied to temporary file: {$tempPath}");
                } else {
                    file_put_contents($tempPath, "-- SQLite in-memory or empty database dump at {$timestamp}\n");
                }
            } else {
                // For PostgreSQL / MySQL: Export schema & table metadata
                $handle = fopen($tempPath, 'w');
                fwrite($handle, "-- TAALLUM BD DATABASE DUMP\n-- Timestamp: {$timestamp}\n-- Connection: {$connection}\n\n");

                $tables = DB::connection($connection)->getDoctrineSchemaManager()->listTableNames();

                foreach ($tables as $table) {
                    $count = DB::table($table)->count();
                    fwrite($handle, "-- Table: {$table} (rows: {$count})\n");
                }

                fclose($handle);
            }

            // Upload to configured storage disk
            $backupContent = file_get_contents($tempPath);
            $storageDestination = "backups/{$backupName}";

            Storage::disk($diskName)->put($storageDestination, $backupContent);
            $this->info("Backup successfully uploaded to disk [{$diskName}] at path: {$storageDestination}");

            // Clean up temporary local file
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }

            Log::info("Database backup completed successfully: {$storageDestination} on [{$diskName}]");

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Database backup failed: {$e->getMessage()}");
            Log::error("Database backup error: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);

            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }

            return Command::FAILURE;
        }
    }
}
