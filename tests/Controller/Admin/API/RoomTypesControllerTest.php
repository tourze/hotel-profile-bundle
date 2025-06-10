<?php

namespace Tourze\HotelProfileBundle\Tests\Controller\Admin\API;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Tourze\HotelProfileBundle\Controller\Admin\API\RoomTypesController;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Entity\RoomType;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;
use Tourze\HotelProfileBundle\Enum\RoomTypeStatusEnum;
use Tourze\HotelProfileBundle\HotelProfileBundle;
use Tourze\IntegrationTestKernel\IntegrationTestKernel;

class RoomTypesControllerTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private RoomTypesController $controller;

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
        $this->controller = static::getContainer()->get(RoomTypesController::class);

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
        // 先清理房型，再清理酒店（因为外键约束）
        $roomTypeRepository = $this->entityManager->getRepository(RoomType::class);
        $roomTypes = $roomTypeRepository->findAll();
        foreach ($roomTypes as $roomType) {
            $this->entityManager->remove($roomType);
        }

        $hotelRepository = $this->entityManager->getRepository(Hotel::class);
        $hotels = $hotelRepository->findAll();
        foreach ($hotels as $hotel) {
            $this->entityManager->remove($hotel);
        }

        $this->entityManager->flush();
    }

    public function test_getRoomTypes_withValidHotelId_returnsRoomTypes(): void
    {
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType1 = $this->createTestRoomType($hotel, '豪华大床房');
        $roomType2 = $this->createTestRoomType($hotel, '标准双床房');

        $this->entityManager->persist($roomType1);
        $this->entityManager->persist($roomType2);
        $this->entityManager->flush();

        $request = new Request(['hotelId' => $hotel->getId()]);

        $response = $this->controller->getRoomTypes($request, $this->entityManager);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertCount(2, $data);

        $roomTypeNames = array_column($data, 'name');
        $this->assertContains('豪华大床房', $roomTypeNames);
        $this->assertContains('标准双床房', $roomTypeNames);

        // 验证返回的数据结构
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('name', $data[0]);
    }

    public function test_getRoomTypes_withNonExistentHotelId_returnsEmptyArray(): void
    {
        $request = new Request(['hotelId' => 99999]);

        $response = $this->controller->getRoomTypes($request, $this->entityManager);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertEmpty($data);
    }

    public function test_getRoomTypes_withoutHotelId_returnsEmptyArray(): void
    {
        $request = new Request();

        $response = $this->controller->getRoomTypes($request, $this->entityManager);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertEmpty($data);
    }

    public function test_getRoomTypes_withEmptyHotelId_returnsEmptyArray(): void
    {
        $request = new Request(['hotelId' => '']);

        $response = $this->controller->getRoomTypes($request, $this->entityManager);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertEmpty($data);
    }

    public function test_getRoomTypes_withMultipleHotels_returnsOnlySpecifiedHotelRoomTypes(): void
    {
        $hotel1 = $this->createTestHotel('酒店1');
        $hotel2 = $this->createTestHotel('酒店2');
        $this->entityManager->persist($hotel1);
        $this->entityManager->persist($hotel2);
        $this->entityManager->flush();

        $roomType1 = $this->createTestRoomType($hotel1, '酒店1房型');
        $roomType2 = $this->createTestRoomType($hotel2, '酒店2房型');

        $this->entityManager->persist($roomType1);
        $this->entityManager->persist($roomType2);
        $this->entityManager->flush();

        $request = new Request(['hotelId' => $hotel1->getId()]);

        $response = $this->controller->getRoomTypes($request, $this->entityManager);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertEquals('酒店1房型', $data[0]['name']);
    }

    public function test_getRoomTypes_resultsSortedByName(): void
    {
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType1 = $this->createTestRoomType($hotel, 'Z房型');
        $roomType2 = $this->createTestRoomType($hotel, 'A房型');
        $roomType3 = $this->createTestRoomType($hotel, 'M房型');

        $this->entityManager->persist($roomType1);
        $this->entityManager->persist($roomType2);
        $this->entityManager->persist($roomType3);
        $this->entityManager->flush();

        $request = new Request(['hotelId' => $hotel->getId()]);

        $response = $this->controller->getRoomTypes($request, $this->entityManager);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertCount(3, $data);

        // 验证按名称排序
        $this->assertEquals('A房型', $data[0]['name']);
        $this->assertEquals('M房型', $data[1]['name']);
        $this->assertEquals('Z房型', $data[2]['name']);
    }

    public function test_getRoomTypes_includesAllStatuses(): void
    {
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $activeRoomType = $this->createTestRoomType($hotel, '可用房型');
        $activeRoomType->setStatus(RoomTypeStatusEnum::ACTIVE);

        $disabledRoomType = $this->createTestRoomType($hotel, '停用房型');
        $disabledRoomType->setStatus(RoomTypeStatusEnum::DISABLED);

        $this->entityManager->persist($activeRoomType);
        $this->entityManager->persist($disabledRoomType);
        $this->entityManager->flush();

        $request = new Request(['hotelId' => $hotel->getId()]);

        $response = $this->controller->getRoomTypes($request, $this->entityManager);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertCount(2, $data); // 应该包含所有状态的房型

        $roomTypeNames = array_column($data, 'name');
        $this->assertContains('可用房型', $roomTypeNames);
        $this->assertContains('停用房型', $roomTypeNames);
    }

    public function test_getRoomTypes_onlyReturnsIdAndName(): void
    {
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType = $this->createTestRoomType($hotel, '测试房型');
        $roomType->setArea(45.5);
        $roomType->setBedType('大床');
        $roomType->setDescription('详细描述');

        $this->entityManager->persist($roomType);
        $this->entityManager->flush();

        $request = new Request(['hotelId' => $hotel->getId()]);

        $response = $this->controller->getRoomTypes($request, $this->entityManager);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertCount(1, $data);

        $roomTypeData = $data[0];
        $this->assertArrayHasKey('id', $roomTypeData);
        $this->assertArrayHasKey('name', $roomTypeData);
        $this->assertCount(2, $roomTypeData); // 只应该有 id 和 name 字段

        $this->assertEquals($roomType->getId(), $roomTypeData['id']);
        $this->assertEquals('测试房型', $roomTypeData['name']);
    }

    private function createTestHotel(string $name = '测试酒店'): Hotel
    {
        $hotel = new Hotel();
        $hotel->setName($name);
        $hotel->setAddress('测试地址');
        $hotel->setStarLevel(5);
        $hotel->setContactPerson('测试联系人');
        $hotel->setPhone('13800000000');
        $hotel->setStatus(HotelStatusEnum::OPERATING);

        return $hotel;
    }

    private function createTestRoomType(Hotel $hotel, string $name = '测试房型'): RoomType
    {
        $roomType = new RoomType();
        $roomType->setHotel($hotel);
        $roomType->setName($name);
        $roomType->setArea(30.0);
        $roomType->setBedType('大床');
        $roomType->setMaxGuests(2);
        $roomType->setBreakfastCount(1);
        $roomType->setStatus(RoomTypeStatusEnum::ACTIVE);

        return $roomType;
    }
} 