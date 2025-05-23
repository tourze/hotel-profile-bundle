<?php

namespace Tourze\HotelProfileBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;
use Tourze\HotelProfileBundle\Repository\HotelRepository;
use Tourze\HotelProfileBundle\Service\HotelImportExportService;

class HotelImportExportServiceExportTest extends TestCase
{
    private HotelImportExportService $service;
    private EntityManagerInterface&MockObject $entityManager;
    private LoggerInterface&MockObject $logger;
    private HotelRepository&MockObject $hotelRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->hotelRepository = $this->createMock(HotelRepository::class);
        
        $this->entityManager
            ->method('getRepository')
            ->with(Hotel::class)
            ->willReturn($this->hotelRepository);
        
        $this->service = new HotelImportExportService(
            $this->entityManager,
            $this->logger
        );
    }

    public function test_exportHotelsToExcel_withEmptyDatabase_createsFileWithHeadersOnly(): void
    {
        $this->hotelRepository
            ->method('findAll')
            ->willReturn([]);
        
        $result = $this->service->exportHotelsToExcel();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('file_path', $result);
        $this->assertArrayHasKey('file_name', $result);
        $this->assertArrayHasKey('disposition', $result);
        
        $this->assertFileExists($result['file_path']);
        $this->assertStringContainsString('酒店列表_', $result['file_name']);
        $this->assertStringEndsWith('.xlsx', $result['file_name']);
        
        // 清理临时文件
        unlink($result['file_path']);
    }

    public function test_exportHotelsToExcel_withHotels_createsFileWithData(): void
    {
        $hotel1 = new Hotel();
        $hotel1->setName('测试酒店1');
        $hotel1->setAddress('北京市朝阳区');
        $hotel1->setStarLevel(5);
        $hotel1->setContactPerson('张经理');
        $hotel1->setPhone('13888888888');
        $hotel1->setEmail('test1@hotel.com');
        $hotel1->setStatus(HotelStatusEnum::OPERATING);
        
        $hotel2 = new Hotel();
        $hotel2->setName('测试酒店2');
        $hotel2->setAddress('上海市浦东新区');
        $hotel2->setStarLevel(4);
        $hotel2->setContactPerson('李经理');
        $hotel2->setPhone('13999999999');
        $hotel2->setEmail('test2@hotel.com');
        $hotel2->setStatus(HotelStatusEnum::SUSPENDED);
        
        $this->hotelRepository
            ->method('findAll')
            ->willReturn([$hotel1, $hotel2]);
        
        $result = $this->service->exportHotelsToExcel();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('file_path', $result);
        $this->assertArrayHasKey('file_name', $result);
        $this->assertArrayHasKey('disposition', $result);
        
        $this->assertFileExists($result['file_path']);
        $this->assertStringContainsString('酒店列表_', $result['file_name']);
        $this->assertStringEndsWith('.xlsx', $result['file_name']);
        
        // 验证文件内容
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($result['file_path']);
        $worksheet = $spreadsheet->getActiveSheet();
        
        // 验证表头
        $this->assertEquals('ID', $worksheet->getCell('A1')->getValue());
        $this->assertEquals('酒店名称', $worksheet->getCell('B1')->getValue());
        $this->assertEquals('详细地址', $worksheet->getCell('C1')->getValue());
        $this->assertEquals('星级', $worksheet->getCell('D1')->getValue());
        $this->assertEquals('联系人', $worksheet->getCell('E1')->getValue());
        $this->assertEquals('联系电话', $worksheet->getCell('F1')->getValue());
        $this->assertEquals('联系邮箱', $worksheet->getCell('G1')->getValue());
        $this->assertEquals('状态', $worksheet->getCell('H1')->getValue());
        
        // 验证数据行
        $this->assertEquals('测试酒店1', $worksheet->getCell('B2')->getValue());
        $this->assertEquals('北京市朝阳区', $worksheet->getCell('C2')->getValue());
        $this->assertEquals(5, $worksheet->getCell('D2')->getValue());
        $this->assertEquals('张经理', $worksheet->getCell('E2')->getValue());
        $this->assertEquals('13888888888', $worksheet->getCell('F2')->getValue());
        $this->assertEquals('test1@hotel.com', $worksheet->getCell('G2')->getValue());
        $this->assertEquals('运营中', $worksheet->getCell('H2')->getValue());
        
        $this->assertEquals('测试酒店2', $worksheet->getCell('B3')->getValue());
        $this->assertEquals('上海市浦东新区', $worksheet->getCell('C3')->getValue());
        $this->assertEquals(4, $worksheet->getCell('D3')->getValue());
        $this->assertEquals('李经理', $worksheet->getCell('E3')->getValue());
        $this->assertEquals('13999999999', $worksheet->getCell('F3')->getValue());
        $this->assertEquals('test2@hotel.com', $worksheet->getCell('G3')->getValue());
        $this->assertEquals('暂停合作', $worksheet->getCell('H3')->getValue());
        
        // 清理临时文件
        unlink($result['file_path']);
    }

    public function test_createImportTemplate_createsTemplateFile(): void
    {
        $result = $this->service->createImportTemplate();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('file_path', $result);
        $this->assertArrayHasKey('file_name', $result);
        $this->assertArrayHasKey('disposition', $result);
        
        $this->assertFileExists($result['file_path']);
        $this->assertEquals('酒店导入模板.xlsx', $result['file_name']);
        
        // 验证模板内容
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($result['file_path']);
        $worksheet = $spreadsheet->getActiveSheet();
        
        // 验证模板表头
        $this->assertEquals('ID (导入时不需填写)', $worksheet->getCell('A1')->getValue());
        $this->assertEquals('酒店名称 (必填)', $worksheet->getCell('B1')->getValue());
        $this->assertEquals('详细地址 (必填)', $worksheet->getCell('C1')->getValue());
        $this->assertEquals('星级 (1-5) (必填)', $worksheet->getCell('D1')->getValue());
        $this->assertEquals('联系人 (必填)', $worksheet->getCell('E1')->getValue());
        $this->assertEquals('联系电话 (必填)', $worksheet->getCell('F1')->getValue());
        $this->assertEquals('联系邮箱', $worksheet->getCell('G1')->getValue());
        $this->assertEquals('状态 (导入时默认为"运营中")', $worksheet->getCell('H1')->getValue());
        
        // 验证没有数据行
        $this->assertNull($worksheet->getCell('A2')->getValue());
        
        // 清理临时文件
        unlink($result['file_path']);
    }

    public function test_exportHotelsToExcel_fileNameFormat_isCorrect(): void
    {
        $this->hotelRepository
            ->method('findAll')
            ->willReturn([]);
        
        $result = $this->service->exportHotelsToExcel();
        
        $fileName = $result['file_name'];
        $this->assertMatchesRegularExpression('/^酒店列表_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.xlsx$/', $fileName);
        
        // 清理临时文件
        unlink($result['file_path']);
    }

    public function test_exportHotelsToExcel_withNullEmailHotel_handlesCorrectly(): void
    {
        $hotel = new Hotel();
        $hotel->setName('测试酒店');
        $hotel->setAddress('测试地址');
        $hotel->setStarLevel(3);
        $hotel->setContactPerson('测试联系人');
        $hotel->setPhone('13800000000');
        $hotel->setEmail(null); // 测试null邮箱
        $hotel->setStatus(HotelStatusEnum::OPERATING);
        
        $this->hotelRepository
            ->method('findAll')
            ->willReturn([$hotel]);
        
        $result = $this->service->exportHotelsToExcel();
        
        $this->assertFileExists($result['file_path']);
        
        // 验证null邮箱的处理
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($result['file_path']);
        $worksheet = $spreadsheet->getActiveSheet();
        
        $this->assertNull($worksheet->getCell('G2')->getValue());
        
        // 清理临时文件
        unlink($result['file_path']);
    }
} 