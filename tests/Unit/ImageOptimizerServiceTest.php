<?php

namespace Tests\Unit;

use App\Services\ImageOptimizerService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageOptimizerServiceTest extends TestCase
{
    public function test_converts_uploaded_image_to_webp_and_stores(): void
    {
        Storage::fake('public');

        $service = new ImageOptimizerService;

        // Create fake image file (600x400)
        $file = UploadedFile::fake()->image('test_avatar.jpg', 600, 400);

        $path = $service->optimizeAndStore($file, 'avatars', 400, 80, 'public');

        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);
    }
}
