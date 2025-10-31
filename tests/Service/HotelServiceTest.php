<?php

namespace Tourze\HotelProfileBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;
use Tourze\HotelProfileBundle\Service\HotelService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * 展示如何使用 AbstractIntegrationTestCase 测试 Service
 *
 * @internal
 */
#[CoversClass(HotelService::class)]
#[RunTestsInSeparateProcesses]
final class HotelServiceTest extends AbstractIntegrationTestCase
{
    private HotelService $service;

    protected function onSetUp(): void
    {
        // 使用类型安全的服务获取
        $this->service = self::getService(HotelService::class);
    }

    public function testFindHotelsByStatusReturnsCorrectHotels(): void
    {
        // Arrange - 获取初始状态（包括Fixtures数据）
        $initialOperatingCount = count($this->service->findHotelsByStatus(HotelStatusEnum::OPERATING));
        $initialSuspendedCount = count($this->service->findHotelsByStatus(HotelStatusEnum::SUSPENDED));

        // 创建新的测试数据
        $operatingHotel1 = $this->createHotel('运营酒店1', HotelStatusEnum::OPERATING);
        $operatingHotel2 = $this->createHotel('运营酒店2', HotelStatusEnum::OPERATING);
        $suspendedHotel = $this->createHotel('暂停酒店', HotelStatusEnum::SUSPENDED);

        // 使用 AbstractIntegrationTestCase 提供的批量持久化方法
        $this->persistEntities([$operatingHotel1, $operatingHotel2, $suspendedHotel]);

        // Act
        $operatingHotels = $this->service->findHotelsByStatus(HotelStatusEnum::OPERATING);
        $suspendedHotels = $this->service->findHotelsByStatus(HotelStatusEnum::SUSPENDED);

        // Assert - 验证增加了正确数量的酒店
        $this->assertCount($initialOperatingCount + 2, $operatingHotels);
        $this->assertCount($initialSuspendedCount + 1, $suspendedHotels);

        // 验证新创建的酒店存在于结果中
        $operatingNames = array_map(fn (Hotel $hotel) => $hotel->getName(), $operatingHotels);
        $this->assertContains('运营酒店1', $operatingNames);
        $this->assertContains('运营酒店2', $operatingNames);

        $suspendedNames = array_map(fn (Hotel $hotel) => $hotel->getName(), $suspendedHotels);
        $this->assertContains('暂停酒店', $suspendedNames);

        // 使用 AbstractIntegrationTestCase 提供的断言方法验证实体已持久化
        foreach ([$operatingHotel1, $operatingHotel2, $suspendedHotel] as $hotel) {
            $this->assertEntityPersisted($hotel);
        }
    }

    public function testUpdateHotelStatusChangesStatusSuccessfully(): void
    {
        // Arrange
        $hotel = $this->createHotel('测试酒店', HotelStatusEnum::OPERATING);
        $this->persistAndFlush($hotel);
        $hotelId = $hotel->getId();
        $this->assertNotNull($hotelId);

        // Act
        $this->service->updateHotelStatus($hotelId, HotelStatusEnum::SUSPENDED);

        // Assert - 清除缓存后重新获取
        self::getEntityManager()->clear();
        $updatedHotel = self::getEntityManager()->find(Hotel::class, $hotelId);

        $this->assertNotNull($updatedHotel);
        $this->assertEquals(HotelStatusEnum::SUSPENDED, $updatedHotel->getStatus());
    }

    public function testFindAllHotels(): void
    {
        // Arrange - 获取初始状态（包括Fixtures数据）
        $initialCount = count($this->service->findAllHotels());

        $hotel1 = $this->createHotel('酒店1', HotelStatusEnum::OPERATING);
        $hotel2 = $this->createHotel('酒店2', HotelStatusEnum::SUSPENDED);
        $this->persistEntities([$hotel1, $hotel2]);

        // Act
        $allHotels = $this->service->findAllHotels();

        // Assert - 验证增加了正确数量的酒店
        $this->assertCount($initialCount + 2, $allHotels);

        // 验证新创建的酒店存在于结果中
        $hotelNames = array_map(fn (Hotel $hotel) => $hotel->getName(), $allHotels);
        $this->assertContains('酒店1', $hotelNames);
        $this->assertContains('酒店2', $hotelNames);
    }

    public function testFindHotelById(): void
    {
        // Arrange
        $hotel = $this->createHotel('测试酒店', HotelStatusEnum::OPERATING);
        $this->persistAndFlush($hotel);
        $hotelId = $hotel->getId();
        $this->assertNotNull($hotelId);

        // Act
        $foundHotel = $this->service->findHotelById($hotelId);

        // Assert
        $this->assertNotNull($foundHotel);
        $this->assertEquals($hotel->getName(), $foundHotel->getName());
    }

    public function testFindHotelByName(): void
    {
        // Arrange
        $hotelName = '测试酒店名称';
        $hotel = $this->createHotel($hotelName, HotelStatusEnum::OPERATING);
        $this->persistAndFlush($hotel);

        // Act
        $foundHotel = $this->service->findHotelByName($hotelName);

        // Assert
        $this->assertNotNull($foundHotel);
        $this->assertEquals($hotelName, $foundHotel->getName());
    }

    /**
     * 创建测试酒店的简单辅助方法
     */
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
}
