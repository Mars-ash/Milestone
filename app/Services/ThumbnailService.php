<?php

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;

class ThumbnailService
{
    public const THUMB_WIDTH = 160;

    public const THUMB_HEIGHT = 120;

    /**
     * Hasilkan byte thumbnail tanpa mengganti byte berkas sumber di storage privat.
     */
    public function getThumbnailBytes(Attachment $attachment): string
    {
        $disk = Storage::disk(config('uploads.disk', 'private'));
        if (! $disk->exists($attachment->storage_key)) {
            return '';
        }

        $sourceBytes = $disk->get($attachment->storage_key);

        if (! function_exists('imagecreatefromstring')) {
            return $sourceBytes;
        }

        $sourceImg = @imagecreatefromstring($sourceBytes);
        if (! $sourceImg) {
            return $sourceBytes;
        }

        $origWidth = imagesx($sourceImg);
        $origHeight = imagesy($sourceImg);

        if ($origWidth <= 0 || $origHeight <= 0) {
            imagedestroy($sourceImg);

            return $sourceBytes;
        }

        // Hitung skala aspect ratio
        $ratio = min(self::THUMB_WIDTH / $origWidth, self::THUMB_HEIGHT / $origHeight);
        $thumbWidth = max(1, (int) round($origWidth * $ratio));
        $thumbHeight = max(1, (int) round($origHeight * $ratio));

        $thumbImg = imagecreatetruecolor($thumbWidth, $thumbHeight);

        // Pertahankan transparansi untuk PNG dan WebP
        imagealphablending($thumbImg, false);
        imagesavealpha($thumbImg, true);
        $transparent = imagecolorallocatealpha($thumbImg, 255, 255, 255, 127);
        imagefilledrectangle($thumbImg, 0, 0, $thumbWidth, $thumbHeight, $transparent);

        imagecopyresampled(
            $thumbImg,
            $sourceImg,
            0,
            0,
            0,
            0,
            $thumbWidth,
            $thumbHeight,
            $origWidth,
            $origHeight
        );

        ob_start();
        if (function_exists('imagewebp')) {
            imagewebp($thumbImg, null, 80);
        } else {
            imagejpeg($thumbImg, null, 80);
        }
        $thumbBytes = ob_get_clean();

        imagedestroy($thumbImg);
        imagedestroy($sourceImg);

        return $thumbBytes ?: $sourceBytes;
    }
}
