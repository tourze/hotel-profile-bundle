<?php

namespace Tourze\HotelProfileBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Tourze\HotelProfileBundle\Service\HotelImportExportService;

class HotelImportExportServiceTest extends TestCase
{
    public function testServiceExists(): void
    {
        $this->assertTrue(class_exists(HotelImportExportService::class));
    }
}