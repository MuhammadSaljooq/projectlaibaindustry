<?php

namespace App\Support;

/**
 * Resolve company logo path and build a data URI for DomPDF.
 * Optionally trims solid white (or default) margins so the mark fills the PDF header better.
 */
final class QuotationPdfLogo
{
    public static function resolvedPath(): ?string
    {
        $company = StatementCompany::normalize(config('company'));
        $logoPath = public_path($company['logo']);
        if (is_file($logoPath)) {
            return $logoPath;
        }
        foreach (['images/laiba_logo.png', 'images/company/laiba-logo.png'] as $rel) {
            $try = public_path($rel);
            if (is_file($try)) {
                return $try;
            }
        }

        return null;
    }

    public static function dataUri(): ?string
    {
        $path = self::resolvedPath();
        if ($path === null || ! is_readable($path)) {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $binary = (string) file_get_contents($path);
        $trimmed = self::tryTrimMargins($binary, $ext);
        if ($trimmed !== null) {
            $binary = $trimmed['binary'];
            $ext = $trimmed['ext'];
        }

        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        return 'data:'.$mime.';base64,'.base64_encode($binary);
    }

    /**
     * @return array{binary: string, ext: string}|null
     */
    private static function tryTrimMargins(string $binary, string $ext): ?array
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagecropauto')) {
            return null;
        }

        $im = @imagecreatefromstring($binary);
        if ($im === false) {
            return null;
        }

        $w0 = imagesx($im);
        $h0 = imagesy($im);
        $mode = defined('IMG_CROP_WHITE') ? IMG_CROP_WHITE : IMG_CROP_DEFAULT;
        $cropped = @imagecropauto($im, $mode);
        if ($cropped === false) {
            imagedestroy($im);

            return null;
        }

        $w1 = imagesx($cropped);
        $h1 = imagesy($cropped);
        if ($w1 >= $w0 && $h1 >= $h0) {
            imagedestroy($im);
            imagedestroy($cropped);

            return null;
        }

        imagedestroy($im);
        $im = $cropped;

        ob_start();
        imagesavealpha($im, true);
        imagealphablending($im, false);
        imagepng($im, null, 6);
        $blob = ob_get_clean();
        imagedestroy($im);

        if ($blob === false || $blob === '') {
            return null;
        }

        return ['binary' => $blob, 'ext' => 'png'];
    }
}
