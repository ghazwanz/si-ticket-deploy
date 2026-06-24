<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\QrCodeService;
use PHPUnit\Framework\TestCase;

class QrCodeServiceTest extends TestCase
{
    /**
     * Test that generateSvg returns a valid SVG string.
     */
    public function test_generate_svg_returns_valid_svg_string(): void
    {
        $service = new QrCodeService;
        $svg = $service->generateSvg('test-token-data');

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('</svg>', $svg);
    }
}
