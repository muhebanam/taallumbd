<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FileUploadService
{
    /**
     * Allowed image MIME types and corresponding extensions.
     */
    protected const ALLOWED_IMAGE_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * Upload and safely re-encode an image to the private disk.
     *
     * @return string Relative storage path
     *
     * @throws ValidationException
     */
    public static function storePrivateImage(
        UploadedFile $file,
        string $directory = 'payment_screenshots',
        int $maxSizeKb = 3072
    ): string {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'screenshot' => 'ফাইল আপলোড ব্যর্থ হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।',
            ]);
        }

        // 1. Size Validation
        if ($file->getSize() > ($maxSizeKb * 1024)) {
            throw ValidationException::withMessages([
                'screenshot' => "ফাইলের আকার সর্বোচ্চ {$maxSizeKb} KB হতে পারে।",
            ]);
        }

        // 2. MIME & Extension validation
        $mime = $file->getMimeType();
        $ext = strtolower($file->getClientOriginalExtension());

        if (! array_key_exists($mime, self::ALLOWED_IMAGE_TYPES)) {
            throw ValidationException::withMessages([
                'screenshot' => 'শুধুমাত্র JPG, PNG অথবা WEBP ফরম্যাটের ছবি আপলোড করা যাবে।',
            ]);
        }

        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            throw ValidationException::withMessages([
                'screenshot' => 'ফাইলের এক্সটেনশনটি গ্রহণযোগ্য নয়।',
            ]);
        }

        $rawContents = file_get_contents($file->getRealPath());

        // Validate binary magic-byte signatures
        self::validateMagicBytes($rawContents, $mime);

        // 3. Image re-encoding (strips polyglots, PHP tags in EXIF, embedded malcode)
        $cleanBinary = null;
        $targetExt = 'png';

        if (extension_loaded('gd') && function_exists('imagecreatefromstring')) {
            $img = @imagecreatefromstring($rawContents);
            if ($img === false) {
                throw ValidationException::withMessages([
                    'screenshot' => 'অবৈধ ইমেজ ফাইল। এটি সঠিক ছবি ফরম্যাট নয়।',
                ]);
            }

            // Re-encode into PNG to strip EXIF and harmful payloads
            ob_start();
            imagealphablending($img, false);
            imagesavealpha($img, true);
            imagepng($img, null, 8);
            $cleanBinary = ob_get_clean();
            imagedestroy($img);
        } else {
            // Fallback header verification
            $cleanBinary = $rawContents;
            $targetExt = self::ALLOWED_IMAGE_TYPES[$mime];
        }

        // 4. Randomized filename
        $fileName = Str::random(40).'.'.$targetExt;
        $path = trim($directory, '/').'/'.$fileName;

        // 5. Store to config-driven private disk
        $diskName = config('filesystems.private_disk', 'local');
        Storage::disk($diskName)->put($path, $cleanBinary);

        return $path;
    }

    /**
     * Get a signed or direct URL to view a private file.
     */
    public static function getPrivateFileUrl(string $path, int $minutes = 60): string
    {
        $diskName = config('filesystems.private_disk', 'local');
        $disk = Storage::disk($diskName);

        // If cloud driver supports temporaryUrl (e.g. S3 / R2)
        try {
            if (method_exists($disk, 'temporaryUrl')) {
                return $disk->temporaryUrl($path, now()->addMinutes($minutes));
            }
        } catch (\Throwable $e) {
            // Fall through to local signed route
        }

        return URL::temporarySignedRoute(
            'private.file.view',
            now()->addMinutes($minutes),
            ['path' => base64_encode($path)]
        );
    }

    /**
     * Validate raw binary magic bytes to prevent file spoofing/polyglot attacks.
     *
     * @throws ValidationException
     */
    protected static function validateMagicBytes(string $bytes, string $mime): void
    {
        if (strlen($bytes) < 12) {
            throw ValidationException::withMessages([
                'screenshot' => 'ফাইলটি করাপ্টেড বা অসম্পূর্ণ।',
            ]);
        }

        $isValid = match ($mime) {
            'image/jpeg' => str_starts_with($bytes, "\xFF\xD8\xFF"),
            'image/png'  => str_starts_with($bytes, "\x89PNG\r\n\x1a\n"),
            'image/webp' => str_starts_with($bytes, 'RIFF') && substr($bytes, 8, 4) === 'WEBP',
            default      => false,
        };

        if (! $isValid) {
            throw ValidationException::withMessages([
                'screenshot' => 'ফাইলের অভ্যন্তরীণ বাইনারি সিগনেচার সঠিক নয়। এটি একটি ভুয়া ইমেজ ফাইল।',
            ]);
        }
    }
}
