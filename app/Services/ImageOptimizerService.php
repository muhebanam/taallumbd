<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ImageOptimizerService
{
    /**
     * Convert an uploaded image to WebP format, resize to max dimensions, and store on disk.
     */
    public function optimizeAndStore(
        UploadedFile $file,
        string $directory = 'uploads',
        ?int $maxWidth = 1200,
        int $quality = 82,
        ?string $disk = null
    ): string {
        $disk = $disk ?? config('filesystems.default', 'public');

        try {
            $mime = $file->getMimeType();
            $path = $file->getRealPath();

            $srcImage = match ($mime) {
                'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
                'image/png' => @imagecreatefrompng($path),
                'image/webp' => @imagecreatefromwebp($path),
                'image/gif' => @imagecreatefromgif($path),
                default => null,
            };

            if (! $srcImage) {
                return $file->store($directory, $disk);
            }

            $origWidth = imagesx($srcImage);
            $origHeight = imagesy($srcImage);

            // Determine target dimensions
            if ($maxWidth && $origWidth > $maxWidth) {
                $targetWidth = $maxWidth;
                $targetHeight = (int) round(($origHeight / $origWidth) * $maxWidth);
            } else {
                $targetWidth = $origWidth;
                $targetHeight = $origHeight;
            }

            $dstImage = imagecreatetruecolor($targetWidth, $targetHeight);

            // Preserve alpha transparency for PNG/WebP
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
            $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
            imagefilledrectangle($dstImage, 0, 0, $targetWidth, $targetHeight, $transparent);

            imagecopyresampled(
                $dstImage,
                $srcImage,
                0,
                0,
                0,
                0,
                $targetWidth,
                $targetHeight,
                $origWidth,
                $origHeight
            );

            // Save to WebP in memory buffer
            ob_start();
            imagewebp($dstImage, null, $quality);
            $webpData = ob_get_clean();

            imagedestroy($srcImage);
            imagedestroy($dstImage);

            if (! empty($webpData)) {
                $filename = Str::random(40).'.webp';
                $targetPath = trim($directory, '/').'/'.$filename;
                Storage::disk($disk)->put($targetPath, $webpData, ['visibility' => 'public']);

                return $targetPath;
            }
        } catch (Throwable $e) {
            // Fallback to default storing if GD processing encounters any issue
        }

        return $file->store($directory, $disk);
    }
}
