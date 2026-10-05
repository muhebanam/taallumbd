<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SyncStorageToR2Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:sync-to-r2
                            {--target-disk=r2_public : Target storage disk name}
                            {--source-dir= : Custom source directory relative to base_path}
                            {--dry-run : Only show files that would be uploaded}
                            {--force : Overwrite existing files on target disk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync local public storage and seeded assets to Cloudflare R2 bucket idempotently';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $targetDisk = (string) $this->option('target-disk');
        $isDryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $this->info("Scanning files to sync to target disk [{$targetDisk}]...");
        if ($isDryRun) {
            $this->warn('DRY RUN MODE ENABLED: No files will actually be uploaded.');
        }

        try {
            $r2 = Storage::disk($targetDisk);
        } catch (\Throwable $e) {
            $this->error("Failed to initialize target disk [{$targetDisk}]: {$e->getMessage()}");

            return Command::FAILURE;
        }

        // Directories to scan
        $directories = [];
        if ($this->option('source-dir')) {
            $directories[] = base_path((string) $this->option('source-dir'));
        } else {
            $directories[] = storage_path('app/public');
            if (File::isDirectory(public_path('images'))) {
                $directories[] = public_path('images');
            }
        }

        $syncedCount = 0;
        $skippedCount = 0;

        foreach ($directories as $dir) {
            if (! File::isDirectory($dir)) {
                continue;
            }

            $files = File::allFiles($dir);
            $this->line('Found '.count($files)." file(s) in [{$dir}].");

            foreach ($files as $file) {
                $relativePath = str_replace('\\', '/', $file->getRelativePathname());

                // If from public/images, prefix with images/
                if ($dir === public_path('images')) {
                    $targetPath = 'images/'.$relativePath;
                } else {
                    $targetPath = $relativePath;
                }

                if ($isDryRun) {
                    $this->line("[Dry Run] Found asset: {$file->getPathname()} -> {$targetPath}");
                    $syncedCount++;

                    continue;
                }

                // Check if already exists on R2
                $exists = false;
                try {
                    $exists = $r2->exists($targetPath);
                } catch (\Throwable $e) {
                    if ($this->output->isVerbose()) {
                        $this->warn("Could not check existence for [{$targetPath}]: {$e->getMessage()}");
                    }
                }

                if ($exists && ! $force) {
                    $skippedCount++;
                    if ($this->output->isVerbose()) {
                        $this->line("Skipping [{$targetPath}] (already exists).");
                    }

                    continue;
                }

                try {
                    $stream = fopen($file->getPathname(), 'r');
                    $r2->put($targetPath, $stream, ['visibility' => 'public']);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                    $syncedCount++;
                    $this->info("Uploaded: {$targetPath}");
                } catch (\Throwable $e) {
                    $this->error("Failed uploading [{$targetPath}]: {$e->getMessage()}");
                }
            }
        }

        $this->newLine();
        $this->info("Sync completed! Synced/Queued: {$syncedCount}, Skipped: {$skippedCount}.");

        return Command::SUCCESS;
    }
}
