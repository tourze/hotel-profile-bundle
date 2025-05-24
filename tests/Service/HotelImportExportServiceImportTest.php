<?php

namespace Tourze\HotelProfileBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Repository\HotelRepository;
use Tourze\HotelProfileBundle\Service\HotelImportExportService;

class HotelImportExportServiceImportTest extends TestCase
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

    public function test_importHotelsFromExcel_withValidData_importsSuccessfully(): void
    {
        $testData = [
            ['ID', '酒店名称', '详细地址', '星级', '联系人', '联系电话', '联系邮箱'],
            ['', '新酒店', '北京市朝阳区', 5, '张经理', '13888888888', 'test@hotel.com']
        ];
        
        $excelFile = $this->createTestExcelFile($testData);
        
        $this->hotelRepository
            ->method('findOneBy')
            ->with(['name' => '新酒店'])
            ->willReturn(null);
        
        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Hotel::class));
        
        $this->entityManager
            ->expects($this->once())
            ->method('flush');
        
        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('创建新酒店', ['name' => '新酒店']);
        
        $result = $this->service->importHotelsFromExcel($excelFile);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['import_count']);
        $this->assertEmpty($result['errors']);
        
        // 清理临时文件
        unlink($excelFile->getPathname());
    }

    public function test_importHotelsFromExcel_withExistingHotel_updatesHotel(): void
    {
        $testData = [
            ['ID', '酒店名称', '详细地址', '星级', '联系人', '联系电话', '联系邮箱'],
            ['', '已存在酒店', '上海市浦东新区', 4, '李经理', '13999999999', 'update@hotel.com']
        ];
        
        $excelFile = $this->createTestExcelFile($testData);
        
        $existingHotel = new Hotel();
        $existingHotel->setName('已存在酒店');
        $existingHotel->setAddress('旧地址');
        $existingHotel->setStarLevel(3);
        $existingHotel->setContactPerson('旧联系人');
        $existingHotel->setPhone('13000000000');
        $existingHotel->setEmail('old@hotel.com');
        
        $this->hotelRepository
            ->method('findOneBy')
            ->with(['name' => '已存在酒店'])
            ->willReturn($existingHotel);
        
        $this->entityManager
            ->expects($this->never())
            ->method('persist');
        
        $this->entityManager
            ->expects($this->once())
            ->method('flush');
        
        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('更新酒店信息', $this->anything());
        
        $result = $this->service->importHotelsFromExcel($excelFile);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['import_count']);
        $this->assertEmpty($result['errors']);
        
        // 验证酒店信息被更新
        $this->assertEquals('上海市浦东新区', $existingHotel->getAddress());
        $this->assertEquals(4, $existingHotel->getStarLevel());
        $this->assertEquals('李经理', $existingHotel->getContactPerson());
        $this->assertEquals('13999999999', $existingHotel->getPhone());
        $this->assertEquals('update@hotel.com', $existingHotel->getEmail());
        
        // 清理临时文件
        unlink($excelFile->getPathname());
    }

    public function test_importHotelsFromExcel_withIncompleteData_returnsErrors(): void
    {
        $testData = [
            ['ID', '酒店名称', '详细地址', '星级', '联系人', '联系电话', '联系邮箱'],
            ['', '', '地址', 5, '联系人', '13888888888', 'test@hotel.com'], // 缺少酒店名称
            ['', '酒店名称', '', 5, '联系人', '13888888888', 'test@hotel.com'], // 缺少地址
            ['', '酒店名称', '地址', 5, '', '13888888888', 'test@hotel.com'], // 缺少联系人
            ['', '酒店名称', '地址', 5, '联系人', '', 'test@hotel.com'] // 缺少电话
        ];
        
        $excelFile = $this->createTestExcelFile($testData);
        
        $this->entityManager
            ->expects($this->never())
            ->method('persist');
        
        $this->entityManager
            ->expects($this->once())
            ->method('flush');
        
        $result = $this->service->importHotelsFromExcel($excelFile);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['import_count']);
        $this->assertCount(4, $result['errors']);
        $this->assertStringContainsString('第 2 行数据不完整', $result['errors'][0]);
        $this->assertStringContainsString('第 3 行数据不完整', $result['errors'][1]);
        $this->assertStringContainsString('第 4 行数据不完整', $result['errors'][2]);
        $this->assertStringContainsString('第 5 行数据不完整', $result['errors'][3]);
        
        // 清理临时文件
        unlink($excelFile->getPathname());
    }

    public function test_importHotelsFromExcel_withInvalidStarLevel_returnsErrors(): void
    {
        $testData = [
            ['ID', '酒店名称', '详细地址', '星级', '联系人', '联系电话', '联系邮箱'],
            ['', '酒店1', '地址1', 0, '联系人1', '13888888888', 'test1@hotel.com'], // 星级太低
            ['', '酒店2', '地址2', 6, '联系人2', '13999999999', 'test2@hotel.com'], // 星级太高
            ['', '酒店3', '地址3', -1, '联系人3', '13777777777', 'test3@hotel.com'] // 负星级
        ];
        
        $excelFile = $this->createTestExcelFile($testData);
        
        $this->entityManager
            ->expects($this->never())
            ->method('persist');
        
        $this->entityManager
            ->expects($this->once())
            ->method('flush');
        
        $result = $this->service->importHotelsFromExcel($excelFile);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['import_count']);
        $this->assertCount(3, $result['errors']);
        $this->assertStringContainsString('第 2 行星级数据无效', $result['errors'][0]);
        $this->assertStringContainsString('第 3 行星级数据无效', $result['errors'][1]);
        $this->assertStringContainsString('第 4 行星级数据无效', $result['errors'][2]);
        
        // 清理临时文件
        unlink($excelFile->getPathname());
    }

    public function test_importHotelsFromExcel_withEmptyFile_returnsNoErrors(): void
    {
        $testData = [
            ['ID', '酒店名称', '详细地址', '星级', '联系人', '联系电话', '联系邮箱']
        ];
        
        $excelFile = $this->createTestExcelFile($testData);
        
        $this->entityManager
            ->expects($this->never())
            ->method('persist');
        
        $this->entityManager
            ->expects($this->once())
            ->method('flush');
        
        $result = $this->service->importHotelsFromExcel($excelFile);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['import_count']);
        $this->assertEmpty($result['errors']);
        
        // 清理临时文件
        unlink($excelFile->getPathname());
    }

    public function test_importHotelsFromExcel_withMixedValidAndInvalidData_processesCorrectly(): void
    {
        $testData = [
            ['ID', '酒店名称', '详细地址', '星级', '联系人', '联系电话', '联系邮箱'],
            ['', '有效酒店1', '北京市朝阳区', 5, '张经理', '13888888888', 'valid1@hotel.com'],
            ['', '', '地址', 4, '联系人', '13999999999', 'invalid@hotel.com'], // 无效：缺少酒店名称
            ['', '有效酒店2', '上海市浦东新区', 3, '李经理', '13777777777', 'valid2@hotel.com'],
            ['', '无效星级', '地址', 0, '联系人', '13666666666', 'invalid2@hotel.com'] // 无效：星级错误
        ];
        
        $excelFile = $this->createTestExcelFile($testData);
        
        $this->hotelRepository
            ->method('findOneBy')
            ->willReturn(null);
        
        $this->entityManager
            ->expects($this->exactly(2))
            ->method('persist')
            ->with($this->isInstanceOf(Hotel::class));
        
        $this->entityManager
            ->expects($this->once())
            ->method('flush');
        
        $result = $this->service->importHotelsFromExcel($excelFile);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['import_count']);
        $this->assertCount(2, $result['errors']);
        $this->assertStringContainsString('第 3 行数据不完整', $result['errors'][0]);
        $this->assertStringContainsString('第 5 行星级数据无效', $result['errors'][1]);
        
        // 清理临时文件
        unlink($excelFile->getPathname());
    }

    private function createTestExcelFile(array $data): UploadedFile
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // 填充数据
        $row = 1;
        foreach ($data as $rowData) {
            $col = 'A';
            foreach ($rowData as $cellData) {
                $sheet->setCellValue($col . $row, $cellData);
                $col++;
            }
            $row++;
        }
        
        // 保存到临时文件
        $tempFile = tempnam(sys_get_temp_dir(), 'hotel_import_test_');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFile);
        
        // 创建 UploadedFile 对象
        return new UploadedFile(
            $tempFile,
            'test_hotels.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }
} 