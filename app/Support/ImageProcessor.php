<?php

namespace App\Support;

use GdImage;

/**
 * Normalizes uploaded photos and scans with GD: fixes phone (EXIF) rotation,
 * flattens transparency onto white and re-encodes as JPEG.
 */
class ImageProcessor
{
    public static function available(): bool
    {
        return function_exists('imagecreatefromstring');
    }

    /** Center-cropped square (portrait images keep their upper part, where the face is). */
    public static function squareJpeg(string $file, int $size = 600): ?string
    {
        $source = self::load($file);
        if ($source === null) {
            return null;
        }

        $width  = imagesx($source);
        $height = imagesy($source);
        $side   = min($width, $height);

        $canvas = self::whiteCanvas($size, $size);
        imagecopyresampled($canvas, $source, 0, 0, intdiv($width - $side, 2), intdiv($height - $side, 4), $size, $size, $side, $side);

        return self::encode($canvas, $source);
    }

    /**
     * Whole image scaled down so its longest side is at most $maxSide (never upscaled).
     *
     * @return array{jpeg: string, width: int, height: int}|null
     */
    public static function fitJpeg(string $file, int $maxSide = 2000): ?array
    {
        $source = self::load($file);
        if ($source === null) {
            return null;
        }

        $width  = imagesx($source);
        $height = imagesy($source);
        $scale  = min(1, $maxSide / max($width, $height));
        $newW   = max(1, (int) round($width * $scale));
        $newH   = max(1, (int) round($height * $scale));

        $canvas = self::whiteCanvas($newW, $newH);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newW, $newH, $width, $height);

        $jpeg = self::encode($canvas, $source);

        return $jpeg === null ? null : ['jpeg' => $jpeg, 'width' => $newW, 'height' => $newH];
    }

    private static function load(string $file): ?GdImage
    {
        $source = @imagecreatefromstring((string) file_get_contents($file));
        if ($source === false) {
            return null;
        }

        // Phone photos and some scanners store rotation in EXIF instead of the pixels.
        if (function_exists('exif_read_data')) {
            $orientation = @exif_read_data($file)['Orientation'] ?? 1;
            $angle = [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0;
            if ($angle !== 0) {
                $rotated = imagerotate($source, $angle, 0);
                imagedestroy($source);
                $source = $rotated;
            }
        }

        return $source;
    }

    private static function whiteCanvas(int $width, int $height): GdImage
    {
        $canvas = imagecreatetruecolor($width, $height);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));

        return $canvas;
    }

    private static function encode(GdImage $canvas, GdImage $source): ?string
    {
        ob_start();
        imagejpeg($canvas, null, 85);
        $jpeg = ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        return $jpeg !== '' && $jpeg !== false ? $jpeg : null;
    }
}
