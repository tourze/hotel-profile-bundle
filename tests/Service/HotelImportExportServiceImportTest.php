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
        $excelFile = $this->createTestExcelFile([
            ['ID', '酒店名称', '详细地址', '星级', '联系人', '联系电话', '联系邮箱', '状态'],
            ['', '测试酒店1', '北京市朝阳区', '5', '张经理', '13888888888', 'test1@hotel.com', ''],
            ['', '测试酒店2', '上海市浦东新区', '4', '李经理', '13999999999', 'test2@hotel.com', '']
        ]);
        
        $this->hotelRepository
            ->method('findOneBy')
            ->willReturn(null); // 没有重复酒店
        
        $this->entityManager
            ->expects($this->exactly(2))
            ->method('persist');
        
        $this->entityManager
            ->expects($this->once())
            ->method('flush');
        
        $this->logger
            ->expects($this->exactly(2))
            ->method('info');
        
        $result = $this->service->importHotelsFromExcel($excelFile);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['import_count']);
        $this->assertEmpty($result['errors']);
        
        // 清理临时文件
        unlink($excelFile->getPathname());
    }

    public function test_importHotelsFromExcel_withExistingHotel_updatesHotel(): void
    {
        $excelFile = $this->createTestExcelFile([
            ['ID', '酒店名称', '详细地址', '星级', '联系人', '联系电话', '联系邮箱', '状态'],
            ['', '现有酒店', '新地址', '5', '新联系人', '13888888888', 'new@hotel.com', '']
        ]);
        
        $existingHotel = new Hotel();
        $existingHotel->setName('现有酒店');
        $existingHotel->setAddress('旧地址');
        
        $this->hotelRepository
            ->method('findOneBy')
            ->with(['name' => '现有酒店'])
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
        
        // 验证酒店信息已更新
        $this->assertEquals('新地址', $existingHotel->getAddress());
        $this->assertEquals(5, $existingHotel->getStarLevel());
        $this->assertEquals('新联系人', $existingHotel->getContactPerson());
        $this->assertEquals('13888888888', $existingHotel->getPhone());
        $this->assertEquals('new@hotel.com', $existingHotel->getEmail());
        
        // 清理临时文件
        unlink($excelFile->getPathname());
    }

    public function test_importHotelsFromExcel_withIncompleteData_returnsErrors(): void
    {
        $excelFile = $this->createTestExcelFile([
            ['ID', '酒店名称', '详细地址', '星级', '联系人', '联系电话', '联系邮箱', '状态'],
            ['', '', '北京市朝阳区', '5', '张经理', '13888888888', 'test@hotel.com', ''], // 缺少酒店名称
            ['', '测试酒店', '', '4', '李经理', '13999999999', 'test2@hotel.com', ''], // 缺少地址
            ['', '测试酒店3', '上海市', '3', '', '13777777777', 'test3@hotel.com', ''], // 缺少联系人
            ['', '测试酒店4', '广州市', '2', '王经理', '', 'test4@hotel.com', ''] // 缺少电话
        ]);
        
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
        $excelFile = $this->createTestExcelFile([
            ['ID', '酒店名称', '详细地址', '星级', '联系人', '联系电话', '联系邮箱', '状态'],
            ['', '测试酒店1', '北京市朝阳区', '0', '张经理', '13888888888', 'test1@hotel.com', ''], // 星级太低
            ['', '测试酒店2', '上海市浦东新区', '6', '李经理', '13999999999', 'test2@hotel.com', ''], // 星级太高
            ['', '测试酒店3', '广州市天河区', 'abc', '王经理', '13777777777', 'test3@hotel.com', ''] // 非数字星级
        ]);
        
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
        $excelFile = $this->createTestExcelFile([
            ['ID', '酒店名称', '详细地址', '星级', '联系人', '联系电话', '联系邮箱', '状态']
        ]);
        
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
        $excelFile = $this->createTestExcelFile([
            ['ID', '酒店名称', '详细地址', '星级', '联系人', '联系电话', '联系邮箱', '状态'],
            ['', '有效酒店1', '北京市朝阳区', '5', '张经理', '13888888888', 'test1@hotel.com', ''],
            ['', '', '上海市浦东新区', '4', '李经理', '13999999999', 'test2@hotel.com', ''], // 无效：缺少名称
            ['', '有效酒店2', '广州市天河区', '3', '王经理', '13777777777', 'test3@hotel.com', '']
        ]);
        
        $this->hotelRepository
            ->method('findOneBy')
            ->willReturn(null);
        
        $this->entityManager
            ->expects($this->exactly(2))
            ->method('persist');
        
        $this->entityManager
            ->expects($this->once())
            ->method('flush');
        
        $this->logger
            ->expects($this->exactly(2))
            ->method('info');
        
        $result = $this->service->importHotelsFromExcel($excelFile);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['import_count']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('第 3 行数据不完整', $result['errors'][0]);
        
        // 清理临时文件
        unlink($excelFile->getPathname());
    }

    private function createTestExcelFile(array $data): UploadedFile
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        foreach ($data as $rowIndex => $rowData) {
            foreach ($rowData as $colIndex => $cellValue) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                $sheet->setCellValue($columnLetter . ($rowIndex + 1), $cellValue);
            }
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'test_excel_');
        $writer->save($tempFile);
        
        return new UploadedFile(
            $tempFile,
            'test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }
} 