<?php

namespace Tourze\HotelProfileBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Entity\RoomType;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;
use Tourze\HotelProfileBundle\Enum\RoomTypeStatusEnum;
use Tourze\HotelProfileBundle\HotelProfileBundle;
use Tourze\HotelProfileBundle\Repository\RoomTypeRepository;
use Tourze\IntegrationTestKernel\IntegrationTestKernel;

class RoomTypeRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private RoomTypeRepository $repository;

    protected static function createKernel(array $options = []): KernelInterface
    {
        $env = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
        $debug = $options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true;

        return new IntegrationTestKernel($env, $debug, [
            HotelProfileBundle::class => ['all' => true],
        ]);
    }

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = static::getContainer()->get(RoomTypeRepository::class);
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
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('DELETE FROM ims_hotel_room_type');
        $connection->executeStatement('DELETE FROM ims_hotel_profile');
    }

    public function test_save_withValidEntity_persistsToDatabase(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType = $this->createTestRoomType($hotel, '豪华大床房');

        // Act
        $this->repository->save($roomType, true);

        // Assert
        $this->assertNotNull($roomType->getId());
        $savedRoomType = $this->repository->find($roomType->getId());
        $this->assertNotNull($savedRoomType);
        $this->assertEquals('豪华大床房', $savedRoomType->getName());
        $this->assertEquals($hotel->getId(), $savedRoomType->getHotel()->getId());
    }

    public function test_save_withFlush_immediatelyPersists(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType = $this->createTestRoomType($hotel);

        // Act
        $this->repository->save($roomType, true);

        // Assert
        $this->assertNotNull($roomType->getId());
        $count = $this->repository->count([]);
        $this->assertEquals(1, $count);
    }

    public function test_save_withoutFlush_doesNotPersistImmediately(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType = $this->createTestRoomType($hotel);

        // Act
        $this->repository->save($roomType, false);

        // Assert
        $this->assertNull($roomType->getId());
        $this->entityManager->flush();
        $this->assertNotNull($roomType->getId());
    }

    public function test_remove_withValidEntity_deletesFromDatabase(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType = $this->createTestRoomType($hotel);
        $this->repository->save($roomType, true);
        $roomTypeId = $roomType->getId();

        // Act
        $this->repository->remove($roomType, true);

        // Assert
        $deletedRoomType = $this->repository->find($roomTypeId);
        $this->assertNull($deletedRoomType);
    }

    public function test_find_withValidId_returnsEntity(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType = $this->createTestRoomType($hotel, '查找测试房型');
        $this->repository->save($roomType, true);

        // Act
        $result = $this->repository->find($roomType->getId());

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('查找测试房型', $result->getName());
    }

    public function test_find_withInvalidId_returnsNull(): void
    {
        // Act
        $result = $this->repository->find(999999);

        // Assert
        $this->assertNull($result);
    }

    public function test_findByHotelId_returnsRoomTypesForSpecificHotel(): void
    {
        // Arrange
        $hotel1 = $this->createTestHotel('酒店1');
        $hotel2 = $this->createTestHotel('酒店2');
        $this->entityManager->persist($hotel1);
        $this->entityManager->persist($hotel2);
        $this->entityManager->flush();

        $roomType1 = $this->createTestRoomType($hotel1, '酒店1房型1');
        $roomType2 = $this->createTestRoomType($hotel1, '酒店1房型2');
        $roomType3 = $this->createTestRoomType($hotel2, '酒店2房型1');

        $this->repository->save($roomType1, true);
        $this->repository->save($roomType2, true);
        $this->repository->save($roomType3, true);

        // Act
        $hotel1RoomTypes = $this->repository->findByHotelId($hotel1->getId());
        $hotel2RoomTypes = $this->repository->findByHotelId($hotel2->getId());

        // Assert
        $this->assertCount(2, $hotel1RoomTypes);
        $this->assertCount(1, $hotel2RoomTypes);

        foreach ($hotel1RoomTypes as $roomType) {
            $this->assertEquals($hotel1->getId(), $roomType->getHotel()->getId());
        }
    }

    public function test_findByHotelId_withNonExistentHotel_returnsEmptyArray(): void
    {
        // Act
        $results = $this->repository->findByHotelId(999999);

        // Assert
        $this->assertEmpty($results);
    }

    public function test_findByNameAndHotelId_returnsMatchingRoomType(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType = $this->createTestRoomType($hotel, '豪华套房');
        $this->repository->save($roomType, true);

        // Act
        $result = $this->repository->findByNameAndHotelId('豪华套房', $hotel->getId());

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('豪华套房', $result->getName());
        $this->assertEquals($hotel->getId(), $result->getHotel()->getId());
    }

    public function test_findByNameAndHotelId_withNoMatch_returnsNull(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        // Act
        $result = $this->repository->findByNameAndHotelId('不存在的房型', $hotel->getId());

        // Assert
        $this->assertNull($result);
    }

    public function test_findActiveRoomTypes_returnsOnlyActiveRoomTypes(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $activeRoomType1 = $this->createTestRoomType($hotel, '可用房型1');
        $activeRoomType1->setStatus(RoomTypeStatusEnum::ACTIVE);

        $activeRoomType2 = $this->createTestRoomType($hotel, '可用房型2');
        $activeRoomType2->setStatus(RoomTypeStatusEnum::ACTIVE);

        $disabledRoomType = $this->createTestRoomType($hotel, '停用房型');
        $disabledRoomType->setStatus(RoomTypeStatusEnum::DISABLED);

        $this->repository->save($activeRoomType1, true);
        $this->repository->save($activeRoomType2, true);
        $this->repository->save($disabledRoomType, true);

        // Act
        $activeRoomTypes = $this->repository->findActiveRoomTypes();

        // Assert
        $this->assertCount(2, $activeRoomTypes);
        foreach ($activeRoomTypes as $roomType) {
            $this->assertEquals(RoomTypeStatusEnum::ACTIVE, $roomType->getStatus());
        }
    }

    public function test_findActiveRoomTypes_withNoActiveRoomTypes_returnsEmptyArray(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $disabledRoomType = $this->createTestRoomType($hotel, '停用房型');
        $disabledRoomType->setStatus(RoomTypeStatusEnum::DISABLED);
        $this->repository->save($disabledRoomType, true);

        // Act
        $results = $this->repository->findActiveRoomTypes();

        // Assert
        $this->assertEmpty($results);
    }

    public function test_findAll_returnsAllRoomTypes(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType1 = $this->createTestRoomType($hotel, '房型1');
        $roomType2 = $this->createTestRoomType($hotel, '房型2');
        $this->repository->save($roomType1, true);
        $this->repository->save($roomType2, true);

        // Act
        $roomTypes = $this->repository->findAll();

        // Assert
        $this->assertCount(2, $roomTypes);
    }

    public function test_findOneBy_returnsMatchingRoomType(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType = $this->createTestRoomType($hotel, '唯一房型');
        $this->repository->save($roomType, true);

        // Act
        $foundRoomType = $this->repository->findOneBy(['name' => '唯一房型']);

        // Assert
        $this->assertNotNull($foundRoomType);
        $this->assertEquals('唯一房型', $foundRoomType->getName());
    }

    public function test_findBy_withCriteria_returnsMatchingRoomTypes(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType1 = $this->createTestRoomType($hotel, '大床房');
        $roomType1->setBedType('大床');
        $roomType2 = $this->createTestRoomType($hotel, '双床房');
        $roomType2->setBedType('双床');
        $roomType3 = $this->createTestRoomType($hotel, '另一个大床房');
        $roomType3->setBedType('大床');

        $this->repository->save($roomType1, true);
        $this->repository->save($roomType2, true);
        $this->repository->save($roomType3, true);

        // Act
        $bigBedRooms = $this->repository->findBy(['bedType' => '大床']);

        // Assert
        $this->assertCount(2, $bigBedRooms);
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
