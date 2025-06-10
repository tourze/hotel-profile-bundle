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
        return new IntegrationTestKernel('test', true, [
            HotelProfileBundle::class => ['all' => true],
        ]);
    }

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = static::getContainer()->get(HotelRepository::class);

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
        $hotels = $this->repository->findAll();
        foreach ($hotels as $hotel) {
            $this->entityManager->remove($hotel);
        }
        $this->entityManager->flush();
    }

    public function test_save_withFlush_persistsHotelToDatabase(): void
    {
        $hotel = new Hotel();
        $hotel->setName('测试酒店');
        $hotel->setAddress('测试地址');
        $hotel->setStarLevel(5);
        $hotel->setContactPerson('测试联系人');
        $hotel->setPhone('13800000000');
        $hotel->setEmail('test@hotel.com');
        $hotel->setStatus(HotelStatusEnum::OPERATING);

        $this->repository->save($hotel, true);

        $this->assertNotNull($hotel->getId());

        // 验证数据已保存到数据库
        $savedHotel = $this->repository->find($hotel->getId());
        $this->assertNotNull($savedHotel);
        $this->assertEquals('测试酒店', $savedHotel->getName());
        $this->assertEquals('测试地址', $savedHotel->getAddress());
    }

    public function test_save_withoutFlush_doesNotPersistImmediately(): void
    {
        $hotel = new Hotel();
        $hotel->setName('测试酒店');
        $hotel->setAddress('测试地址');
        $hotel->setStarLevel(5);
        $hotel->setContactPerson('测试联系人');
        $hotel->setPhone('13800000000');

        $this->repository->save($hotel, false);

        // 在flush之前，ID应该为null
        $this->assertNull($hotel->getId());

        $this->entityManager->flush();

        // flush后ID应该有值
        $this->assertNotNull($hotel->getId());
    }

    public function test_remove_withFlush_deletesHotelFromDatabase(): void
    {
        $hotel = $this->createTestHotel();
        $this->repository->save($hotel, true);
        $hotelId = $hotel->getId();

        $this->repository->remove($hotel, true);

        $deletedHotel = $this->repository->find($hotelId);
        $this->assertNull($deletedHotel);
    }

    public function test_findByName_withExactMatch_returnsHotel(): void
    {
        $hotel1 = $this->createTestHotel('豪华大酒店', '北京');
        $hotel2 = $this->createTestHotel('商务酒店', '上海');

        $this->repository->save($hotel1, true);
        $this->repository->save($hotel2, true);

        $results = $this->repository->findByName('豪华大酒店');

        $this->assertCount(1, $results);
        $this->assertEquals('豪华大酒店', $results[0]->getName());
    }

    public function test_findByName_withPartialMatch_returnsMatchingHotels(): void
    {
        $hotel1 = $this->createTestHotel('豪华大酒店', '北京');
        $hotel2 = $this->createTestHotel('豪华商务酒店', '上海');
        $hotel3 = $this->createTestHotel('快捷酒店', '广州');

        $this->repository->save($hotel1, true);
        $this->repository->save($hotel2, true);
        $this->repository->save($hotel3, true);

        $results = $this->repository->findByName('豪华');

        $this->assertCount(2, $results);
        $hotelNames = array_map(fn($hotel) => $hotel->getName(), $results);
        $this->assertContains('豪华大酒店', $hotelNames);
        $this->assertContains('豪华商务酒店', $hotelNames);
    }

    public function test_findByName_withNoMatch_returnsEmptyArray(): void
    {
        $hotel = $this->createTestHotel('测试酒店', '测试地址');
        $this->repository->save($hotel, true);

        $results = $this->repository->findByName('不存在的酒店');

        $this->assertEmpty($results);
    }

    public function test_findByStarLevel_returnsHotelsWithSpecifiedStarLevel(): void
    {
        $hotel1 = $this->createTestHotel('五星酒店1', '地址1', 5);
        $hotel2 = $this->createTestHotel('五星酒店2', '地址2', 5);
        $hotel3 = $this->createTestHotel('四星酒店', '地址3', 4);

        $this->repository->save($hotel1, true);
        $this->repository->save($hotel2, true);
        $this->repository->save($hotel3, true);

        $fiveStarHotels = $this->repository->findByStarLevel(5);
        $fourStarHotels = $this->repository->findByStarLevel(4);

        $this->assertCount(2, $fiveStarHotels);
        $this->assertCount(1, $fourStarHotels);

        foreach ($fiveStarHotels as $hotel) {
            $this->assertEquals(5, $hotel->getStarLevel());
        }
    }

    public function test_findByStarLevel_withNoMatch_returnsEmptyArray(): void
    {
        $hotel = $this->createTestHotel('三星酒店', '地址', 3);
        $this->repository->save($hotel, true);

        $results = $this->repository->findByStarLevel(5);

        $this->assertEmpty($results);
    }

    public function test_findOperatingHotels_returnsOnlyOperatingHotels(): void
    {
        $operatingHotel1 = $this->createTestHotel('运营酒店1', '地址1');
        $operatingHotel1->setStatus(HotelStatusEnum::OPERATING);

        $operatingHotel2 = $this->createTestHotel('运营酒店2', '地址2');
        $operatingHotel2->setStatus(HotelStatusEnum::OPERATING);

        $suspendedHotel = $this->createTestHotel('暂停酒店', '地址3');
        $suspendedHotel->setStatus(HotelStatusEnum::SUSPENDED);

        $this->repository->save($operatingHotel1, true);
        $this->repository->save($operatingHotel2, true);
        $this->repository->save($suspendedHotel, true);

        $operatingHotels = $this->repository->findOperatingHotels();

        $this->assertCount(2, $operatingHotels);

        foreach ($operatingHotels as $hotel) {
            $this->assertEquals(HotelStatusEnum::OPERATING, $hotel->getStatus());
        }
    }

    public function test_findOperatingHotels_withNoOperatingHotels_returnsEmptyArray(): void
    {
        $suspendedHotel = $this->createTestHotel('暂停酒店', '地址');
        $suspendedHotel->setStatus(HotelStatusEnum::SUSPENDED);
        $this->repository->save($suspendedHotel, true);

        $results = $this->repository->findOperatingHotels();

        $this->assertEmpty($results);
    }

    public function test_findAll_returnsAllHotels(): void
    {
        $hotel1 = $this->createTestHotel('酒店1', '地址1');
        $hotel2 = $this->createTestHotel('酒店2', '地址2');

        $this->repository->save($hotel1, true);
        $this->repository->save($hotel2, true);

        $hotels = $this->repository->findAll();

        $this->assertCount(2, $hotels);
    }

    public function test_findOneBy_returnsMatchingHotel(): void
    {
        $hotel = $this->createTestHotel('唯一酒店', '唯一地址');
        $this->repository->save($hotel, true);

        $foundHotel = $this->repository->findOneBy(['name' => '唯一酒店']);

        $this->assertNotNull($foundHotel);
        $this->assertEquals('唯一酒店', $foundHotel->getName());
    }

    public function test_findBy_withCriteria_returnsMatchingHotels(): void
    {
        $hotel1 = $this->createTestHotel('北京酒店', '北京地址', 5);
        $hotel2 = $this->createTestHotel('上海酒店', '上海地址', 5);
        $hotel3 = $this->createTestHotel('广州酒店', '广州地址', 4);

        $this->repository->save($hotel1, true);
        $this->repository->save($hotel2, true);
        $this->repository->save($hotel3, true);

        $fiveStarHotels = $this->repository->findBy(['starLevel' => 5]);

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
