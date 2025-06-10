<?php

namespace Tourze\HotelProfileBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;
use Tourze\HotelProfileBundle\HotelProfileBundle;
use Tourze\HotelProfileBundle\Repository\HotelRepository;
use Tourze\IntegrationTestKernel\IntegrationTestKernel;

class HotelRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private HotelRepository $repository;

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
        $this->repository = static::getContainer()->get(HotelRepository::class);
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
        $hotel = $this->createTestHotel('测试酒店', '测试地址');

        // Act
        $this->repository->save($hotel, true);

        // Assert
        $this->assertNotNull($hotel->getId());
        $savedHotel = $this->repository->find($hotel->getId());
        $this->assertNotNull($savedHotel);
        $this->assertEquals('测试酒店', $savedHotel->getName());
        $this->assertEquals('测试地址', $savedHotel->getAddress());
    }

    public function test_save_withFlush_immediatelyPersists(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();

        // Act
        $this->repository->save($hotel, true);

        // Assert
        $this->assertNotNull($hotel->getId());
        $count = $this->repository->count([]);
        $this->assertEquals(1, $count);
    }

    public function test_save_withoutFlush_doesNotPersistImmediately(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();

        // Act
        $this->repository->save($hotel, false);

        // Assert
        $this->assertNull($hotel->getId());
        $this->entityManager->flush();
        $this->assertNotNull($hotel->getId());
    }

    public function test_remove_withValidEntity_deletesFromDatabase(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->repository->save($hotel, true);
        $hotelId = $hotel->getId();

        // Act
        $this->repository->remove($hotel, true);

        // Assert
        $deletedHotel = $this->repository->find($hotelId);
        $this->assertNull($deletedHotel);
    }

    public function test_find_withValidId_returnsEntity(): void
    {
        // Arrange
        $hotel = $this->createTestHotel('查找测试酒店');
        $this->repository->save($hotel, true);

        // Act
        $result = $this->repository->find($hotel->getId());

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('查找测试酒店', $result->getName());
    }

    public function test_find_withInvalidId_returnsNull(): void
    {
        // Act
        $result = $this->repository->find(999999);

        // Assert
        $this->assertNull($result);
    }

    public function test_findByName_withExactMatch_returnsHotels(): void
    {
        // Arrange
        $hotel1 = $this->createTestHotel('豪华大酒店');
        $hotel2 = $this->createTestHotel('商务酒店');
        $this->repository->save($hotel1, true);
        $this->repository->save($hotel2, true);

        // Act
        $results = $this->repository->findByName('豪华大酒店');

        // Assert
        $this->assertCount(1, $results);
        $this->assertEquals('豪华大酒店', $results[0]->getName());
    }

    public function test_findByName_withPartialMatch_returnsMatchingHotels(): void
    {
        // Arrange
        $hotel1 = $this->createTestHotel('豪华大酒店');
        $hotel2 = $this->createTestHotel('豪华商务酒店');
        $hotel3 = $this->createTestHotel('快捷酒店');
        $this->repository->save($hotel1, true);
        $this->repository->save($hotel2, true);
        $this->repository->save($hotel3, true);

        // Act
        $results = $this->repository->findByName('豪华');

        // Assert
        $this->assertCount(2, $results);
        $hotelNames = array_map(fn($hotel) => $hotel->getName(), $results);
        $this->assertContains('豪华大酒店', $hotelNames);
        $this->assertContains('豪华商务酒店', $hotelNames);
    }

    public function test_findByName_withNoMatch_returnsEmptyArray(): void
    {
        // Arrange
        $hotel = $this->createTestHotel('测试酒店');
        $this->repository->save($hotel, true);

        // Act
        $results = $this->repository->findByName('不存在的酒店');

        // Assert
        $this->assertEmpty($results);
    }

    public function test_findByStarLevel_returnsCorrectStarLevelHotels(): void
    {
        // Arrange
        $hotel1 = $this->createTestHotel('五星酒店1', '地址1', 5);
        $hotel2 = $this->createTestHotel('五星酒店2', '地址2', 5);
        $hotel3 = $this->createTestHotel('四星酒店', '地址3', 4);
        $this->repository->save($hotel1, true);
        $this->repository->save($hotel2, true);
        $this->repository->save($hotel3, true);

        // Act
        $fiveStarHotels = $this->repository->findByStarLevel(5);
        $fourStarHotels = $this->repository->findByStarLevel(4);

        // Assert
        $this->assertCount(2, $fiveStarHotels);
        $this->assertCount(1, $fourStarHotels);
        foreach ($fiveStarHotels as $hotel) {
            $this->assertEquals(5, $hotel->getStarLevel());
        }
    }

    public function test_findByStarLevel_withNoMatch_returnsEmptyArray(): void
    {
        // Arrange
        $hotel = $this->createTestHotel('三星酒店', '地址', 3);
        $this->repository->save($hotel, true);

        // Act
        $results = $this->repository->findByStarLevel(5);

        // Assert
        $this->assertEmpty($results);
    }

    public function test_findOperatingHotels_returnsOnlyOperatingHotels(): void
    {
        // Arrange
        $operatingHotel1 = $this->createTestHotel('运营酒店1');
        $operatingHotel1->setStatus(HotelStatusEnum::OPERATING);

        $operatingHotel2 = $this->createTestHotel('运营酒店2');
        $operatingHotel2->setStatus(HotelStatusEnum::OPERATING);

        $suspendedHotel = $this->createTestHotel('暂停酒店');
        $suspendedHotel->setStatus(HotelStatusEnum::SUSPENDED);

        $this->repository->save($operatingHotel1, true);
        $this->repository->save($operatingHotel2, true);
        $this->repository->save($suspendedHotel, true);

        // Act
        $operatingHotels = $this->repository->findOperatingHotels();

        // Assert
        $this->assertCount(2, $operatingHotels);
        foreach ($operatingHotels as $hotel) {
            $this->assertEquals(HotelStatusEnum::OPERATING, $hotel->getStatus());
        }
    }

    public function test_findOperatingHotels_withNoOperatingHotels_returnsEmptyArray(): void
    {
        // Arrange
        $suspendedHotel = $this->createTestHotel('暂停酒店');
        $suspendedHotel->setStatus(HotelStatusEnum::SUSPENDED);
        $this->repository->save($suspendedHotel, true);

        // Act
        $results = $this->repository->findOperatingHotels();

        // Assert
        $this->assertEmpty($results);
    }

    public function test_findAll_returnsAllHotels(): void
    {
        // Arrange
        $hotel1 = $this->createTestHotel('酒店1');
        $hotel2 = $this->createTestHotel('酒店2');
        $this->repository->save($hotel1, true);
        $this->repository->save($hotel2, true);

        // Act
        $hotels = $this->repository->findAll();

        // Assert
        $this->assertCount(2, $hotels);
    }

    public function test_findOneBy_returnsMatchingHotel(): void
    {
        // Arrange
        $hotel = $this->createTestHotel('唯一酒店');
        $this->repository->save($hotel, true);

        // Act
        $foundHotel = $this->repository->findOneBy(['name' => '唯一酒店']);

        // Assert
        $this->assertNotNull($foundHotel);
        $this->assertEquals('唯一酒店', $foundHotel->getName());
    }

    public function test_findBy_withCriteria_returnsMatchingHotels(): void
    {
        // Arrange
        $hotel1 = $this->createTestHotel('北京酒店', '北京地址', 5);
        $hotel2 = $this->createTestHotel('上海酒店', '上海地址', 5);
        $hotel3 = $this->createTestHotel('广州酒店', '广州地址', 4);
        $this->repository->save($hotel1, true);
        $this->repository->save($hotel2, true);
        $this->repository->save($hotel3, true);

        // Act
        $fiveStarHotels = $this->repository->findBy(['starLevel' => 5]);

        // Assert
        $this->assertCount(2, $fiveStarHotels);
    }

    private function createTestHotel(string $name = '测试酒店', string $address = '测试地址', int $starLevel = 3): Hotel
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
}
