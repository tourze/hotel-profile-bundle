<?php

namespace Tourze\HotelProfileBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tourze\HotelProfileBundle\Service\HotelImportExportService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(HotelImportExportService::class)]
#[RunTestsInSeparateProcesses]
final class HotelImportExportServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试设置逻辑
    }

    public function testCreateImportTemplate(): void
    {
        $service = self::getService(HotelImportExportService::class);
        $result = $service->createImportTemplate();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('file_path', $result);
        $this->assertArrayHasKey('file_name', $result);
        $this->assertArrayHasKey('disposition', $result);

        $this->assertFileExists($result['file_path']);
        $this->assertStringContainsString('酒店导入模板.xlsx', $result['file_name']);
    }

    public function testExportHotelsToExcel(): void
    {
        $service = self::getService(HotelImportExportService::class);
        $result = $service->exportHotelsToExcel();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('file_path', $result);
        $this->assertArrayHasKey('file_name', $result);
        $this->assertArrayHasKey('disposition', $result);

        $this->assertFileExists($result['file_path']);
        $this->assertStringContainsString('酒店列表_', $result['file_name']);
        $this->assertStringEndsWith('.xlsx', $result['file_name']);
    }

    public function testImportHotelsFromExcel(): void
    {
        $service = self::getService(HotelImportExportService::class);

        // 创建一个临时 Excel 文件用于测试
        $tempFile = tempnam(sys_get_temp_dir(), 'test_hotel_');
        $templateResult = $service->createImportTemplate();
        copy($templateResult['file_path'], $tempFile);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'test_hotels.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $result = $service->importHotelsFromExcel($uploadedFile);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('import_count', $result);
        $this->assertArrayHasKey('errors', $result);

        $this->assertTrue($result['success']);
        $this->assertIsInt($result['import_count']);
        $this->assertIsArray($result['errors']);
    }
}
