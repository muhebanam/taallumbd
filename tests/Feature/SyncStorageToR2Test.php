<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SyncStorageToR2Test extends TestCase
{
    public function test_sync_command_dry_run_lists_files_without_uploading(): void
    {
        Storage::fake('r2_test');

        // Ensure temp folder has at least one file
        $tempDir = storage_path('app/public/test_sync');
        File::ensureDirectoryExists($tempDir);
        File::put($tempDir.'/sample.txt', 'hello-r2');

        $this->artisan('storage:sync-to-r2', [
            '--target-disk' => 'r2_test',
            '--source-dir' => 'storage/app/public/test_sync',
            '--dry-run' => true,
        ])
            ->expectsOutputToContain('DRY RUN MODE ENABLED')
            ->assertSuccessful();

        Storage::disk('r2_test')->assertMissing('sample.txt');

        // Cleanup temp file
        File::deleteDirectory($tempDir);
    }

    public function test_sync_command_uploads_files_to_target_disk(): void
    {
        Storage::fake('r2_test');

        $tempDir = storage_path('app/public/test_sync_upload');
        File::ensureDirectoryExists($tempDir);
        File::put($tempDir.'/upload_me.txt', 'cloud-storage-content');

        $this->artisan('storage:sync-to-r2', [
            '--target-disk' => 'r2_test',
            '--source-dir' => 'storage/app/public/test_sync_upload',
        ])
            ->expectsOutputToContain('Uploaded: upload_me.txt')
            ->assertSuccessful();

        Storage::disk('r2_test')->assertExists('upload_me.txt');

        // Test idempotency: running again should skip existing file unless --force
        $this->artisan('storage:sync-to-r2', [
            '--target-disk' => 'r2_test',
            '--source-dir' => 'storage/app/public/test_sync_upload',
        ])
            ->assertSuccessful();

        // Cleanup
        File::deleteDirectory($tempDir);
    }
}
