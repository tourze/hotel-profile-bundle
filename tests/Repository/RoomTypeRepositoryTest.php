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
        return new IntegrationTestKernel('test', true, [
            HotelProfileBundle::class => ['all' => true],
        ]);
    }

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = static::getContainer()->get(RoomTypeRepository::class);

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
        $roomTypes = $this->repository->findAll();
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

    public function test_save_withFlush_persistsRoomTypeToDatabase(): void
    {
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType = new RoomType();
        $roomType->setHotel($hotel);
        $roomType->setName('豪华大床房');
        $roomType->setArea(45.5);
        $roomType->setBedType('特大床');
        $roomType->setMaxGuests(2);
        $roomType->setBreakfastCount(2);
        $roomType->setStatus(RoomTypeStatusEnum::ACTIVE);

        $this->repository->save($roomType, true);

        $this->assertNotNull($roomType->getId());

        // 验证数据已保存到数据库
        $savedRoomType = $this->repository->find($roomType->getId());
        $this->assertNotNull($savedRoomType);
        $this->assertEquals('豪华大床房', $savedRoomType->getName());
        $this->assertEquals(45.5, $savedRoomType->getArea());
    }

    public function test_save_withoutFlush_doesNotPersistImmediately(): void
    {
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType = new RoomType();
        $roomType->setHotel($hotel);
        $roomType->setName('测试房型');
        $roomType->setArea(30.0);
        $roomType->setBedType('大床');
        $roomType->setMaxGuests(2);
        $roomType->setBreakfastCount(1);

        $this->repository->save($roomType, false);

        // 在flush之前，ID应该为null
        $this->assertNull($roomType->getId());

        $this->entityManager->flush();

        // flush后ID应该有值
        $this->assertNotNull($roomType->getId());
    }

    public function test_remove_withFlush_deletesRoomTypeFromDatabase(): void
    {
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType = $this->createTestRoomType($hotel);
        $this->repository->save($roomType, true);
        $roomTypeId = $roomType->getId();

        $this->repository->remove($roomType, true);

        $deletedRoomType = $this->repository->find($roomTypeId);
        $this->assertNull($deletedRoomType);
    }

    public function test_findByHotelId_returnsRoomTypesForSpecificHotel(): void
    {
        $hotel1 = $this->createTestHotel('酒店1');
        $hotel2 = $this->createTestHotel('酒店2');
        $this->entityManager->persist($hotel1);
        $this->entityManager->persist($hotel2);
        $this->entityManager->flush();

        $roomType1 = $this->createTestRoomType($hotel1, '房型1');
        $roomType2 = $this->createTestRoomType($hotel1, '房型2');
        $roomType3 = $this->createTestRoomType($hotel2, '房型3');

        $this->repository->save($roomType1, true);
        $this->repository->save($roomType2, true);
        $this->repository->save($roomType3, true);

        $hotel1RoomTypes = $this->repository->findByHotelId($hotel1->getId());
        $hotel2RoomTypes = $this->repository->findByHotelId($hotel2->getId());

        $this->assertCount(2, $hotel1RoomTypes);
        $this->assertCount(1, $hotel2RoomTypes);

        foreach ($hotel1RoomTypes as $roomType) {
            $this->assertEquals($hotel1->getId(), $roomType->getHotel()->getId());
        }
    }

    public function test_findByHotelId_withNonExistentHotel_returnsEmptyArray(): void
    {
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType = $this->createTestRoomType($hotel);
        $this->repository->save($roomType, true);

        $results = $this->repository->findByHotelId(999999); // 不存在的酒店ID

        $this->assertEmpty($results);
    }

    public function test_findByNameAndHotelId_returnsMatchingRoomType(): void
    {
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType1 = $this->createTestRoomType($hotel, '豪华大床房');
        $roomType2 = $this->createTestRoomType($hotel, '标准双床房');

        $this->repository->save($roomType1, true);
        $this->repository->save($roomType2, true);

        $foundRoomType = $this->repository->findByNameAndHotelId('豪华大床房', $hotel->getId());

        $this->assertNotNull($foundRoomType);
        $this->assertEquals('豪华大床房', $foundRoomType->getName());
        $this->assertEquals($hotel->getId(), $foundRoomType->getHotel()->getId());
    }

    public function test_findByNameAndHotelId_withNoMatch_returnsNull(): void
    {
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType = $this->createTestRoomType($hotel, '豪华房');
        $this->repository->save($roomType, true);

        $result = $this->repository->findByNameAndHotelId('不存在的房型', $hotel->getId());

        $this->assertNull($result);
    }

    public function test_findActiveRoomTypes_returnsOnlyActiveRoomTypes(): void
    {
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

        $activeRoomTypes = $this->repository->findActiveRoomTypes();

        $this->assertCount(2, $activeRoomTypes);

        foreach ($activeRoomTypes as $roomType) {
            $this->assertEquals(RoomTypeStatusEnum::ACTIVE, $roomType->getStatus());
        }
    }

    public function test_findActiveRoomTypes_withNoActiveRoomTypes_returnsEmptyArray(): void
    {
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $disabledRoomType = $this->createTestRoomType($hotel, '停用房型');
        $disabledRoomType->setStatus(RoomTypeStatusEnum::DISABLED);
        $this->repository->save($disabledRoomType, true);

        $results = $this->repository->findActiveRoomTypes();

        $this->assertEmpty($results);
    }

    public function test_findAll_returnsAllRoomTypes(): void
    {
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType1 = $this->createTestRoomType($hotel, '房型1');
        $roomType2 = $this->createTestRoomType($hotel, '房型2');

        $this->repository->save($roomType1, true);
        $this->repository->save($roomType2, true);

        $roomTypes = $this->repository->findAll();

        $this->assertCount(2, $roomTypes);
    }

    public function test_findOneBy_returnsMatchingRoomType(): void
    {
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType = $this->createTestRoomType($hotel, '唯一房型');
        $this->repository->save($roomType, true);

        $foundRoomType = $this->repository->findOneBy(['name' => '唯一房型']);

        $this->assertNotNull($foundRoomType);
        $this->assertEquals('唯一房型', $foundRoomType->getName());
    }

    public function test_findBy_withCriteria_returnsMatchingRoomTypes(): void
    {
        $hotel = $this->createTestHotel();
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        $roomType1 = $this->createTestRoomType($hotel, '大床房1');
        $roomType1->setBedType('大床');

        $roomType2 = $this->createTestRoomType($hotel, '大床房2');
        $roomType2->setBedType('大床');

        $roomType3 = $this->createTestRoomType($hotel, '双床房');
        $roomType3->setBedType('双床');

        $this->repository->save($roomType1, true);
        $this->repository->save($roomType2, true);
        $this->repository->save($roomType3, true);

        $bigBedRoomTypes = $this->repository->findBy(['bedType' => '大床']);

        $this->assertCount(2, $bigBedRoomTypes);
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
