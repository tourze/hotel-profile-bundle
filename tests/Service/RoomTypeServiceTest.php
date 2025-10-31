<?php

namespace Tourze\HotelProfileBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Entity\RoomType;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;
use Tourze\HotelProfileBundle\Enum\RoomTypeStatusEnum;
use Tourze\HotelProfileBundle\Service\RoomTypeService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * 房型服务测试
 *
 * @internal
 */
#[CoversClass(RoomTypeService::class)]
#[RunTestsInSeparateProcesses]
final class RoomTypeServiceTest extends AbstractIntegrationTestCase
{
    private RoomTypeService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(RoomTypeService::class);
    }

    public function testGetAllRoomTypesWithHotelReturnsAllRoomTypesWithHotelInfo(): void
    {
        // Arrange - 获取初始状态（包括Fixtures数据）
        $initialCount = count($this->service->getAllRoomTypesWithHotel());

        $hotel1 = $this->createHotel('测试酒店1');
        $hotel2 = $this->createHotel('测试酒店2');

        $roomType1 = $this->createRoomType($hotel1, '标准单人间');
        $roomType2 = $this->createRoomType($hotel1, '豪华双人间');
        $roomType3 = $this->createRoomType($hotel2, '商务套房');

        $this->persistEntities([$hotel1, $hotel2, $roomType1, $roomType2, $roomType3]);

        // Act
        $result = $this->service->getAllRoomTypesWithHotel();

        // Assert - 验证增加了正确数量的房型
        $this->assertCount($initialCount + 3, $result);
        $this->assertInstanceOf(RoomType::class, $result[0]);
        $this->assertNotNull($result[0]->getHotel());

        // 验证新创建的房型存在于结果中
        $roomTypeNames = array_map(fn (RoomType $rt) => $rt->getName(), $result);
        $this->assertContains('标准单人间', $roomTypeNames);
        $this->assertContains('豪华双人间', $roomTypeNames);
        $this->assertContains('商务套房', $roomTypeNames);
    }

    public function testFindAllRoomTypesReturnsAllRoomTypes(): void
    {
        // Arrange - 获取初始状态（包括Fixtures数据）
        $initialCount = count($this->service->findAllRoomTypes());

        $hotel = $this->createHotel('测试酒店');
        $roomType1 = $this->createRoomType($hotel, '标准间');
        $roomType2 = $this->createRoomType($hotel, '豪华间');

        $this->persistEntities([$hotel, $roomType1, $roomType2]);

        // Act
        $result = $this->service->findAllRoomTypes();

        // Assert - 验证增加了正确数量的房型
        $this->assertCount($initialCount + 2, $result);

        // 验证新创建的房型存在于结果中
        $roomTypeNames = array_map(fn (RoomType $rt) => $rt->getName(), $result);
        $this->assertContains('标准间', $roomTypeNames);
        $this->assertContains('豪华间', $roomTypeNames);
    }

    public function testFindRoomTypesByStatusReturnsCorrectRoomTypes(): void
    {
        // Arrange - 获取初始状态（包括Fixtures数据）
        $initialActiveCount = count($this->service->findRoomTypesByStatus(RoomTypeStatusEnum::ACTIVE));
        $initialDisabledCount = count($this->service->findRoomTypesByStatus(RoomTypeStatusEnum::DISABLED));

        $hotel = $this->createHotel('测试酒店');
        $activeRoom = $this->createRoomType($hotel, '可用房型', RoomTypeStatusEnum::ACTIVE);
        $disabledRoom = $this->createRoomType($hotel, '停用房型', RoomTypeStatusEnum::DISABLED);

        $this->persistEntities([$hotel, $activeRoom, $disabledRoom]);

        // Act
        $activeRooms = $this->service->findRoomTypesByStatus(RoomTypeStatusEnum::ACTIVE);
        $disabledRooms = $this->service->findRoomTypesByStatus(RoomTypeStatusEnum::DISABLED);

        // Assert - 验证增加了正确数量的房型
        $this->assertCount($initialActiveCount + 1, $activeRooms);
        $this->assertCount($initialDisabledCount + 1, $disabledRooms);

        // 验证新创建的房型存在于结果中
        $activeNames = array_map(fn (RoomType $rt) => $rt->getName(), $activeRooms);
        $this->assertContains('可用房型', $activeNames);

        $disabledNames = array_map(fn (RoomType $rt) => $rt->getName(), $disabledRooms);
        $this->assertContains('停用房型', $disabledNames);
    }

    public function testFindRoomTypesByHotelReturnsCorrectRoomTypes(): void
    {
        // Arrange
        $hotel1 = $this->createHotel('酒店1');
        $hotel2 = $this->createHotel('酒店2');

        $room1 = $this->createRoomType($hotel1, '酒店1房型1');
        $room2 = $this->createRoomType($hotel1, '酒店1房型2');
        $room3 = $this->createRoomType($hotel2, '酒店2房型1');

        $this->persistEntities([$hotel1, $hotel2, $room1, $room2, $room3]);

        // Act
        $hotel1Id = $hotel1->getId();
        $hotel2Id = $hotel2->getId();
        $this->assertNotNull($hotel1Id);
        $this->assertNotNull($hotel2Id);
        $hotel1Rooms = $this->service->findRoomTypesByHotel($hotel1Id);
        $hotel2Rooms = $this->service->findRoomTypesByHotel($hotel2Id);

        // Assert
        $this->assertCount(2, $hotel1Rooms);
        $this->assertCount(1, $hotel2Rooms);
    }

    public function testFindRoomTypeByIdReturnsCorrectRoomType(): void
    {
        // Arrange
        $hotel = $this->createHotel('测试酒店');
        $roomType = $this->createRoomType($hotel, '测试房型');
        $this->persistEntities([$hotel, $roomType]);

        // Act
        $roomTypeId = $roomType->getId();
        $this->assertNotNull($roomTypeId);
        $result = $this->service->findRoomTypeById($roomTypeId);

        // Assert
        $this->assertInstanceOf(RoomType::class, $result);
        $this->assertEquals('测试房型', $result->getName());
    }

    public function testFindRoomTypeByIdReturnsNullForNonExistentId(): void
    {
        // Act
        $result = $this->service->findRoomTypeById(999999);

        // Assert
        $this->assertNull($result);
    }

    public function testFindRoomTypesByIdsReturnsCorrectRoomTypes(): void
    {
        // Arrange
        $hotel = $this->createHotel('测试酒店');
        $room1 = $this->createRoomType($hotel, '房型1');
        $room2 = $this->createRoomType($hotel, '房型2');
        $room3 = $this->createRoomType($hotel, '房型3');

        $this->persistEntities([$hotel, $room1, $room2, $room3]);

        // Act
        $room1Id = $room1->getId();
        $room3Id = $room3->getId();
        $this->assertNotNull($room1Id);
        $this->assertNotNull($room3Id);
        $result = $this->service->findRoomTypesByIds([$room1Id, $room3Id]);

        // Assert
        $this->assertCount(2, $result);
        $resultNames = array_map(fn (RoomType $rt) => $rt->getName(), $result);
        $this->assertContains('房型1', $resultNames);
        $this->assertContains('房型3', $resultNames);
    }

    public function testFindRoomTypesByIdsReturnsEmptyArrayForEmptyIds(): void
    {
        // Act
        $result = $this->service->findRoomTypesByIds([]);

        // Assert
        $this->assertEmpty($result);
    }

    public function testFindRoomTypesByHotelAndStatusReturnsCorrectRoomTypes(): void
    {
        // Arrange
        $hotel1 = $this->createHotel('酒店1');
        $hotel2 = $this->createHotel('酒店2');

        $room1 = $this->createRoomType($hotel1, '酒店1可用', RoomTypeStatusEnum::ACTIVE);
        $room2 = $this->createRoomType($hotel1, '酒店1停用', RoomTypeStatusEnum::DISABLED);
        $room3 = $this->createRoomType($hotel2, '酒店2可用', RoomTypeStatusEnum::ACTIVE);

        $this->persistEntities([$hotel1, $hotel2, $room1, $room2, $room3]);

        // Act
        $hotel1Id = $hotel1->getId();
        $this->assertNotNull($hotel1Id);
        $result = $this->service->findRoomTypesByHotelAndStatus($hotel1Id, RoomTypeStatusEnum::ACTIVE);

        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals('酒店1可用', $result[0]->getName());
    }

    public function testFindRoomTypeByHotelAndNameReturnsCorrectRoomType(): void
    {
        // Arrange
        $hotel = $this->createHotel('测试酒店');
        $roomType = $this->createRoomType($hotel, '特定房型');
        $this->persistEntities([$hotel, $roomType]);

        // Act
        $hotelId = $hotel->getId();
        $this->assertNotNull($hotelId);
        $result = $this->service->findRoomTypeByHotelAndName($hotelId, '特定房型');

        // Assert
        $this->assertInstanceOf(RoomType::class, $result);
        $this->assertEquals('特定房型', $result->getName());
    }

    public function testFindRoomTypeByHotelAndNameReturnsNullForNonExistent(): void
    {
        // Arrange
        $hotel = $this->createHotel('测试酒店');
        $this->persistEntities([$hotel]);

        // Act
        $hotelId = $hotel->getId();
        $this->assertNotNull($hotelId);
        $result = $this->service->findRoomTypeByHotelAndName($hotelId, '不存在的房型');

        // Assert
        $this->assertNull($result);
    }

    private function createHotel(string $name, HotelStatusEnum $status = HotelStatusEnum::OPERATING): Hotel
    {
        $hotel = new Hotel();
        $hotel->setName($name);
        $hotel->setAddress('测试地址');
        $hotel->setStarLevel(3);
        $hotel->setContactPerson('测试联系人');
        $hotel->setPhone('13800000000');
        $hotel->setStatus($status);

        return $hotel;
    }

    private function createRoomType(
        Hotel $hotel,
        string $name,
        RoomTypeStatusEnum $status = RoomTypeStatusEnum::ACTIVE,
    ): RoomType {
        $roomType = new RoomType();
        $roomType->setHotel($hotel);
        $roomType->setName($name);
        $roomType->setCode(strtoupper(substr($name, 0, 3)));
        $roomType->setArea(25.0);
        $roomType->setBedType('大床');
        $roomType->setMaxGuests(2);
        $roomType->setStatus($status);

        return $roomType;
    }
}
