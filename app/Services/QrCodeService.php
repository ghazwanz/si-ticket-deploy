<?php

declare(strict_types=1);

namespace App\Services;

use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * Service to generate QR codes.
 */
class QrCodeService
{
    /**
     * Generate QR code as an inline SVG string.
     */
    public function generateSvg(string $data): string
    {
        $options = new QROptions([
            'version' => 5,
            'outputInterface' => QRMarkupSVG::class,
            'eccLevel' => 'L',
            'addQuietzone' => true,
            'imageBase64' => false, // Not used in v6
            'outputBase64' => false,
        ]);

        return (new QRCode($options))->render($data);
    }

    /**
     * Generate QR code as raw PNG binary data.
     */
    public function generatePng(string $data): string
    {
        $options = new QROptions([
            'version' => 5,
            'outputInterface' => QRGdImagePNG::class,
            'eccLevel' => 'L',
            'addQuietzone' => true,
        ]);

        return (new QRCode($options))->render($data);
    }
}
