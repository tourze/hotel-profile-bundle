<?php

namespace Tourze\HotelProfileBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;
use Tourze\HotelProfileBundle\HotelProfileBundle;
use Tourze\HotelProfileBundle\Repository\HotelRepository;
use Tourze\HotelProfileBundle\Service\HotelImportExportService;
use Tourze\IntegrationTestKernel\IntegrationTestKernel;

class HotelImportExportServiceIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private HotelRepository $hotelRepository;
    private HotelImportExportService $service;

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new IntegrationTestKernel('test', true, [
            HotelProfileBundle::class => ['all' => true],
        ]);
    }

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->hotelRepository = static::getContainer()->get(HotelRepository::class);
        $this->service = static::getContainer()->get(HotelImportExportService::class);

        // 清理数据库
        $this->cleanDatabase();
    }

    protected function tearDown(): void
    {
        $this->cleanDatabase();
        self::ensureKernelShutdown();
        parent::tearDown();
    }

    private function cleanDatabase(): void
    {
        $hotels = $this->hotelRepository->findAll();
        foreach ($hotels as $hotel) {
            $this->entityManager->remove($hotel);
        }
        $this->entityManager->flush();
    }

    public function test_exportHotelsToExcel_withRealData_createsCorrectFile(): void
    {
        // 创建测试数据
        $hotel1 = $this->createTestHotel('集成测试酒店1', '北京市朝阳区', 5);
        $hotel2 = $this->createTestHotel('集成测试酒店2', '上海市浦东新区', 4);

        $this->hotelRepository->save($hotel1, true);
        $this->hotelRepository->save($hotel2, true);

        // 执行导出
        $result = $this->service->exportHotelsToExcel();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('file_path', $result);
        $this->assertArrayHasKey('file_name', $result);
        $this->assertFileExists($result['file_path']);

        // 验证文件内容
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($result['file_path']);
        $worksheet = $spreadsheet->getActiveSheet();

        // 验证数据
        $this->assertEquals('集成测试酒店1', $worksheet->getCell('B2')->getValue());
        $this->assertEquals('北京市朝阳区', $worksheet->getCell('C2')->getValue());
        $this->assertEquals(5, $worksheet->getCell('D2')->getValue());

        $this->assertEquals('集成测试酒店2', $worksheet->getCell('B3')->getValue());
        $this->assertEquals('上海市浦东新区', $worksheet->getCell('C3')->getValue());
        $this->assertEquals(4, $worksheet->getCell('D3')->getValue());

        // 清理临时文件
        unlink($result['file_path']);
    }

    public function test_importHotelsFromExcel_withRealDatabase_persistsData(): void
    {
        $testData = [
            ['ID', '酒店名称', '详细地址', '星级', '联系人', '联系电话', '联系邮箱'],
            ['', '集成测试新酒店', '广州市天河区', 4, '王经理', '13800138000', 'integration@test.com']
        ];

        $excelFile = $this->createTestExcelFile($testData);

        $result = $this->service->importHotelsFromExcel($excelFile);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['import_count']);
        $this->assertEmpty($result['errors']);

        // 验证数据已保存到数据库
        $savedHotel = $this->hotelRepository->findOneBy(['name' => '集成测试新酒店']);
        $this->assertNotNull($savedHotel);
        $this->assertEquals('广州市天河区', $savedHotel->getAddress());
        $this->assertEquals(4, $savedHotel->getStarLevel());
        $this->assertEquals('王经理', $savedHotel->getContactPerson());
        $this->assertEquals('13800138000', $savedHotel->getPhone());
        $this->assertEquals('integration@test.com', $savedHotel->getEmail());
        $this->assertEquals(HotelStatusEnum::OPERATING, $savedHotel->getStatus());

        // 清理临时文件
        unlink($excelFile->getPathname());
    }

    public function test_importHotelsFromExcel_updateExistingHotel_modifiesRealData(): void
    {
        // 先创建一个酒店
        $existingHotel = $this->createTestHotel('已存在的酒店', '旧地址', 3);
        $existingHotel->setContactPerson('旧联系人');
        $existingHotel->setPhone('13000000000');
        $existingHotel->setEmail('old@hotel.com');

        $this->hotelRepository->save($existingHotel, true);
        $hotelId = $existingHotel->getId();

        // 准备导入数据
        $testData = [
            ['ID', '酒店名称', '详细地址', '星级', '联系人', '联系电话', '联系邮箱'],
            ['', '已存在的酒店', '新地址', 5, '新联系人', '13999999999', 'new@hotel.com']
        ];

        $excelFile = $this->createTestExcelFile($testData);

        $result = $this->service->importHotelsFromExcel($excelFile);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['import_count']);
        $this->assertEmpty($result['errors']);

        // 验证数据已更新
        $this->entityManager->refresh($existingHotel);
        $this->assertEquals('新地址', $existingHotel->getAddress());
        $this->assertEquals(5, $existingHotel->getStarLevel());
        $this->assertEquals('新联系人', $existingHotel->getContactPerson());
        $this->assertEquals('13999999999', $existingHotel->getPhone());
        $this->assertEquals('new@hotel.com', $existingHotel->getEmail());

        // 验证ID没有变化
        $this->assertEquals($hotelId, $existingHotel->getId());

        // 清理临时文件
        unlink($excelFile->getPathname());
    }

    public function test_importHotelsFromExcel_withInvalidData_doesNotPersist(): void
    {
        $testData = [
            ['ID', '酒店名称', '详细地址', '星级', '联系人', '联系电话', '联系邮箱'],
            ['', '', '地址', 5, '联系人', '13888888888', 'test@hotel.com'], // 无效：缺少酒店名称
            ['', '酒店', '地址', 0, '联系人', '13777777777', 'test2@hotel.com'] // 无效：星级错误
        ];

        $excelFile = $this->createTestExcelFile($testData);

        // 记录导入前的酒店数量
        $initialCount = count($this->hotelRepository->findAll());

        $result = $this->service->importHotelsFromExcel($excelFile);

        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['import_count']);
        $this->assertCount(2, $result['errors']);

        // 验证没有新数据被保存
        $finalCount = count($this->hotelRepository->findAll());
        $this->assertEquals($initialCount, $finalCount);

        // 清理临时文件
        unlink($excelFile->getPathname());
    }

    public function test_exportAndImport_roundTrip_preservesData(): void
    {
        // 创建原始数据
        $originalHotel = $this->createTestHotel('往返测试酒店', '测试地址', 4);
        $originalHotel->setContactPerson('测试联系人');
        $originalHotel->setPhone('13800000000');
        $originalHotel->setEmail('roundtrip@test.com');

        $this->hotelRepository->save($originalHotel, true);

        // 导出数据
        $exportResult = $this->service->exportHotelsToExcel();

        // 清空数据库
        $this->entityManager->remove($originalHotel);
        $this->entityManager->flush();

        // 使用导出的文件创建 UploadedFile
        $uploadedFile = new UploadedFile(
            $exportResult['file_path'],
            'export.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        // 导入数据
        $importResult = $this->service->importHotelsFromExcel($uploadedFile);

        $this->assertTrue($importResult['success']);
        $this->assertEquals(1, $importResult['import_count']);

        // 验证数据一致性
        $importedHotel = $this->hotelRepository->findOneBy(['name' => '往返测试酒店']);
        $this->assertNotNull($importedHotel);
        $this->assertEquals('测试地址', $importedHotel->getAddress());
        $this->assertEquals(4, $importedHotel->getStarLevel());
        $this->assertEquals('测试联系人', $importedHotel->getContactPerson());
        $this->assertEquals('13800000000', $importedHotel->getPhone());
        $this->assertEquals('roundtrip@test.com', $importedHotel->getEmail());

        // 清理临时文件
        unlink($exportResult['file_path']);
    }

    public function test_createImportTemplate_createsValidFile(): void
    {
        $result = $this->service->createImportTemplate();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('file_path', $result);
        $this->assertArrayHasKey('file_name', $result);
        $this->assertFileExists($result['file_path']);
        $this->assertEquals('酒店导入模板.xlsx', $result['file_name']);

        // 验证模板可以被读取
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($result['file_path']);
        $worksheet = $spreadsheet->getActiveSheet();

        // 验证表头
        $this->assertEquals('酒店名称 (必填)', $worksheet->getCell('B1')->getValue());

        // 清理临时文件
        unlink($result['file_path']);
    }

    private function createTestHotel(string $name, string $address, int $starLevel): Hotel
    {
        $hotel = new Hotel();
        $hotel->setName($name);
        $hotel->setAddress($address);
        $hotel->setStarLevel($starLevel);
        $hotel->setContactPerson('测试联系人');
        $hotel->setPhone('13800000000');
        $hotel->setStatus(HotelStatusEnum::OPERATING);

        return $hotel;
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
        $tempFile = tempnam(sys_get_temp_dir(), 'hotel_integration_test_');
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
