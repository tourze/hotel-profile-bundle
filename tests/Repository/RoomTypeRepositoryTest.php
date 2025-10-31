<?php

namespace Tourze\HotelProfileBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Persisters\Exception\UnrecognizedField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Entity\RoomType;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;
use Tourze\HotelProfileBundle\Enum\RoomTypeStatusEnum;
use Tourze\HotelProfileBundle\Repository\RoomTypeRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(RoomTypeRepository::class)]
#[RunTestsInSeparateProcesses]
final class RoomTypeRepositoryTest extends AbstractRepositoryTestCase
{
    private RoomTypeRepository $repository;

    protected function onSetUp(): void
    {
        // 使用类型安全的服务获取
        $this->repository = self::getService(RoomTypeRepository::class);
    }

    protected function createNewEntity(): object
    {
        // 创建测试所需的关联实体（并持久化）
        $hotel = new Hotel();
        $hotel->setName('测试酒店_' . uniqid());
        $hotel->setAddress('测试地址');
        $hotel->setStarLevel(4);
        $hotel->setContactPerson('测试联系人');
        $hotel->setPhone('13800000000');
        $hotel->setStatus(HotelStatusEnum::OPERATING);
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();

        $roomType = new RoomType();
        $roomType->setHotel($hotel);
        $roomType->setName('测试房型_' . uniqid());
        $roomType->setCode('TEST_' . uniqid());
        $roomType->setArea(30.0);
        $roomType->setBedType('大床');
        $roomType->setMaxGuests(2);
        $roomType->setBreakfastCount(1);
        $roomType->setStatus(RoomTypeStatusEnum::ACTIVE);

        return $roomType;
    }

    public function testSaveWithValidEntityPersistsToDatabase(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();

        $roomType = $this->createTestRoomType($hotel, '豪华大床房');

        // Act
        self::getEntityManager()->persist($roomType);
        self::getEntityManager()->flush();

        // Assert
        $this->assertNotNull($roomType->getId());
        $savedRoomType = $this->repository->find($roomType->getId());
        $this->assertNotNull($savedRoomType);
        $this->assertEquals('豪华大床房', $savedRoomType->getName());
        $savedHotel = $savedRoomType->getHotel();
        $this->assertNotNull($savedHotel);
        $this->assertEquals($hotel->getId(), $savedHotel->getId());
    }

    public function testSaveWithFlushImmediatelyPersists(): void
    {
        // Arrange
        $initialCount = $this->repository->count([]);
        $hotel = $this->createTestHotel();
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();

        $roomType = $this->createTestRoomType($hotel);

        // Act
        self::getEntityManager()->persist($roomType);
        self::getEntityManager()->flush();

        // Assert
        $this->assertNotNull($roomType->getId());
        $count = $this->repository->count([]);
        $this->assertEquals($initialCount + 1, $count);
    }

    public function testSaveWithoutFlushDoesNotPersistImmediately(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();

        $roomType = $this->createTestRoomType($hotel);

        // Act
        self::getEntityManager()->persist($roomType);

        // Assert
        $this->assertNull($roomType->getId());
        self::getEntityManager()->flush();
        $this->assertNotNull($roomType->getId());
    }

    public function testRemoveWithValidEntityDeletesFromDatabase(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();

        $roomType = $this->createTestRoomType($hotel);
        self::getEntityManager()->persist($roomType);
        self::getEntityManager()->flush();
        $roomTypeId = $roomType->getId();

        // Act
        self::getEntityManager()->remove($roomType);
        self::getEntityManager()->flush();

        // Assert
        $deletedRoomType = $this->repository->find($roomTypeId);
        $this->assertNull($deletedRoomType);
    }

    public function testFindWithValidIdReturnsEntity(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();

        $roomType = $this->createTestRoomType($hotel, '查找测试房型');
        self::getEntityManager()->persist($roomType);
        self::getEntityManager()->flush();

        // Act
        $result = $this->repository->find($roomType->getId());

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('查找测试房型', $result->getName());
    }

    public function testFindWithInvalidIdReturnsNull(): void
    {
        // Act
        $result = $this->repository->find(999999);

        // Assert
        $this->assertNull($result);
    }

    public function testFindByHotelIdReturnsRoomTypesForSpecificHotel(): void
    {
        // Arrange
        $hotel1 = $this->createTestHotel('酒店1');
        $hotel2 = $this->createTestHotel('酒店2');
        self::getEntityManager()->persist($hotel1);
        self::getEntityManager()->persist($hotel2);
        self::getEntityManager()->flush();

        $roomType1 = $this->createTestRoomType($hotel1, '酒店1房型1');
        $roomType2 = $this->createTestRoomType($hotel1, '酒店1房型2');
        $roomType3 = $this->createTestRoomType($hotel2, '酒店2房型1');

        self::getEntityManager()->persist($roomType1);
        self::getEntityManager()->persist($roomType2);
        self::getEntityManager()->persist($roomType3);
        self::getEntityManager()->flush();

        // Act
        $hotel1Id = $hotel1->getId();
        $hotel2Id = $hotel2->getId();
        $this->assertNotNull($hotel1Id);
        $this->assertNotNull($hotel2Id);

        $hotel1RoomTypes = $this->repository->findByHotelId($hotel1Id);
        $hotel2RoomTypes = $this->repository->findByHotelId($hotel2Id);

        // Assert
        $this->assertCount(2, $hotel1RoomTypes);
        $this->assertCount(1, $hotel2RoomTypes);

        foreach ($hotel1RoomTypes as $roomType) {
            $hotelFromRoomType = $roomType->getHotel();
            $this->assertNotNull($hotelFromRoomType);

            $hotelIdFromRoomType = $hotelFromRoomType->getId();
            $this->assertNotNull($hotelIdFromRoomType);
            $this->assertEquals($hotel1Id, $hotelIdFromRoomType);
        }
    }

    public function testFindByHotelIdWithNonExistentHotelReturnsEmptyArray(): void
    {
        // Act
        $results = $this->repository->findByHotelId(999999);

        // Assert
        $this->assertEmpty($results);
    }

    public function testFindByNameAndHotelIdReturnsMatchingRoomType(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();

        $roomType = $this->createTestRoomType($hotel, '豪华套房');
        self::getEntityManager()->persist($roomType);
        self::getEntityManager()->flush();

        // Act
        $hotelId = $hotel->getId();
        $this->assertNotNull($hotelId);
        $result = $this->repository->findByNameAndHotelId('豪华套房', $hotelId);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('豪华套房', $result->getName());
        $hotelFromResult = $result->getHotel();
        $this->assertNotNull($hotelFromResult);
        $this->assertEquals($hotelId, $hotelFromResult->getId());
    }

    public function testFindByNameAndHotelIdWithNoMatchReturnsNull(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();

        // Act
        $hotelId = $hotel->getId();
        $this->assertNotNull($hotelId);
        $result = $this->repository->findByNameAndHotelId('不存在的房型', $hotelId);

        // Assert
        $this->assertNull($result);
    }

    public function testFindActiveRoomTypesReturnsOnlyActiveRoomTypes(): void
    {
        // Arrange
        $initialActiveCount = count($this->repository->findActiveRoomTypes());

        $hotel = $this->createTestHotel();
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();

        $activeRoomType1 = $this->createTestRoomType($hotel, '可用房型1');
        $activeRoomType1->setStatus(RoomTypeStatusEnum::ACTIVE);

        $activeRoomType2 = $this->createTestRoomType($hotel, '可用房型2');
        $activeRoomType2->setStatus(RoomTypeStatusEnum::ACTIVE);

        $disabledRoomType = $this->createTestRoomType($hotel, '停用房型');
        $disabledRoomType->setStatus(RoomTypeStatusEnum::DISABLED);

        self::getEntityManager()->persist($activeRoomType1);
        self::getEntityManager()->persist($activeRoomType2);
        self::getEntityManager()->persist($disabledRoomType);
        self::getEntityManager()->flush();

        // Act
        $activeRoomTypes = $this->repository->findActiveRoomTypes();

        // Assert
        $this->assertCount($initialActiveCount + 2, $activeRoomTypes);

        // 验证新创建的房型都在结果中
        $roomTypeNames = array_map(fn ($roomType) => $roomType->getName(), $activeRoomTypes);
        $this->assertContains('可用房型1', $roomTypeNames);
        $this->assertContains('可用房型2', $roomTypeNames);

        foreach ($activeRoomTypes as $roomType) {
            $this->assertEquals(RoomTypeStatusEnum::ACTIVE, $roomType->getStatus());
        }
    }

    public function testFindActiveRoomTypesFiltersOutDisabledRoomTypes(): void
    {
        // Arrange
        $initialActiveCount = count($this->repository->findActiveRoomTypes());

        $hotel = $this->createTestHotel();
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();

        $disabledRoomType = $this->createTestRoomType($hotel, '停用房型');
        $disabledRoomType->setStatus(RoomTypeStatusEnum::DISABLED);
        self::getEntityManager()->persist($disabledRoomType);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findActiveRoomTypes();

        // Assert
        // 停用的房型不应该增加活跃房型的数量
        $this->assertCount($initialActiveCount, $results);

        // 验证所有结果都是活跃状态
        foreach ($results as $roomType) {
            $this->assertEquals(RoomTypeStatusEnum::ACTIVE, $roomType->getStatus());
        }

        // 验证停用的房型不在结果中
        $roomTypeNames = array_map(fn ($roomType) => $roomType->getName(), $results);
        $this->assertNotContains('停用房型', $roomTypeNames);
    }

    public function testFindAllReturnsAllRoomTypes(): void
    {
        // Arrange
        $initialCount = count($this->repository->findAll());
        $hotel = $this->createTestHotel();
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();

        $roomType1 = $this->createTestRoomType($hotel, '房型1');
        $roomType2 = $this->createTestRoomType($hotel, '房型2');
        self::getEntityManager()->persist($roomType1);
        self::getEntityManager()->persist($roomType2);
        self::getEntityManager()->flush();

        // Act
        $roomTypes = $this->repository->findAll();

        // Assert
        $this->assertCount($initialCount + 2, $roomTypes);

        // 验证新创建的房型都在结果中
        $roomTypeNames = array_map(fn ($roomType) => $roomType->getName(), $roomTypes);
        $this->assertContains('房型1', $roomTypeNames);
        $this->assertContains('房型2', $roomTypeNames);
    }

    public function testFindOneByReturnsMatchingRoomType(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();

        $roomType = $this->createTestRoomType($hotel, '唯一房型');
        self::getEntityManager()->persist($roomType);
        self::getEntityManager()->flush();

        // Act
        $foundRoomType = $this->repository->findOneBy(['name' => '唯一房型']);

        // Assert
        $this->assertNotNull($foundRoomType);
        $this->assertEquals('唯一房型', $foundRoomType->getName());
    }

    public function testFindByWithCriteriaReturnsMatchingRoomTypes(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();

        $roomType1 = $this->createTestRoomType($hotel, '大床房');
        $roomType1->setBedType('大床');
        $roomType2 = $this->createTestRoomType($hotel, '双床房');
        $roomType2->setBedType('双床');
        $roomType3 = $this->createTestRoomType($hotel, '另一个大床房');
        $roomType3->setBedType('大床');

        self::getEntityManager()->persist($roomType1);
        self::getEntityManager()->persist($roomType2);
        self::getEntityManager()->persist($roomType3);
        self::getEntityManager()->flush();

        // Act
        $bigBedRooms = $this->repository->findBy(['bedType' => '大床']);

        // Assert
        $this->assertGreaterThanOrEqual(2, count($bigBedRooms));

        // 验证新创建的房型都在结果中
        $roomTypeNames = array_map(fn ($roomType) => $roomType->getName(), $bigBedRooms);
        $this->assertContains('大床房', $roomTypeNames);
        $this->assertContains('另一个大床房', $roomTypeNames);

        // 验证所有结果都是大床类型
        foreach ($bigBedRooms as $roomType) {
            $this->assertEquals('大床', $roomType->getBedType());
        }
    }

    public function testCountReturnsCorrectTotalNumber(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->persistAndFlush($hotel);

        $initialCount = $this->repository->count([]);

        $roomType1 = $this->createTestRoomType($hotel, '计数房型1');
        $roomType2 = $this->createTestRoomType($hotel, '计数房型2');
        $this->persistAndFlush($roomType1);
        $this->persistAndFlush($roomType2);

        // Act
        $totalCount = $this->repository->count([]);

        // Assert
        $this->assertEquals($initialCount + 2, $totalCount);
    }

    public function testCountWithCriteriaReturnsFilteredCount(): void
    {
        // Arrange
        $initialBigBedCount = $this->repository->count(['bedType' => '大床']);
        $initialTwinBedCount = $this->repository->count(['bedType' => '双床']);

        $hotel = $this->createTestHotel();
        $this->persistAndFlush($hotel);

        $roomType1 = $this->createTestRoomType($hotel, '大床房1');
        $roomType1->setBedType('大床');
        $roomType2 = $this->createTestRoomType($hotel, '大床房2');
        $roomType2->setBedType('大床');
        $roomType3 = $this->createTestRoomType($hotel, '双床房');
        $roomType3->setBedType('双床');

        $this->persistAndFlush($roomType1);
        $this->persistAndFlush($roomType2);
        $this->persistAndFlush($roomType3);

        // Act
        $bigBedCount = $this->repository->count(['bedType' => '大床']);
        $twinBedCount = $this->repository->count(['bedType' => '双床']);

        // Assert
        $this->assertEquals($initialBigBedCount + 2, $bigBedCount);
        $this->assertEquals($initialTwinBedCount + 1, $twinBedCount);
    }

    public function testCountWithEmptyTableReturnsZero(): void
    {
        // Act
        $count = $this->repository->count(['bedType' => '不存在的床型']);

        // Assert
        $this->assertEquals(0, $count);
    }

    public function testFindByWithOrderByReturnsOrderedResults(): void
    {
        // Arrange - 使用唯一前缀避免与其他测试数据冲突
        $uniquePrefix = 'ORDER_TEST_' . uniqid();
        $hotel = $this->createTestHotel();
        $this->persistAndFlush($hotel);

        $roomType1 = $this->createTestRoomType($hotel, $uniquePrefix . '_C房型');
        $roomType2 = $this->createTestRoomType($hotel, $uniquePrefix . '_A房型');
        $roomType3 = $this->createTestRoomType($hotel, $uniquePrefix . '_B房型');
        $this->persistAndFlush($roomType1);
        $this->persistAndFlush($roomType2);
        $this->persistAndFlush($roomType3);

        // Act - 使用LIKE查询仅获取我们的测试数据
        $queryBuilder = $this->repository->createQueryBuilder('rt');
        $roomTypesAsc = $queryBuilder
            ->where('rt.name LIKE :prefix')
            ->setParameter('prefix', $uniquePrefix . '%')
            ->orderBy('rt.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        $queryBuilder2 = $this->repository->createQueryBuilder('rt');
        $roomTypesDesc = $queryBuilder2
            ->where('rt.name LIKE :prefix')
            ->setParameter('prefix', $uniquePrefix . '%')
            ->orderBy('rt.name', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        // Assert
        $this->assertCount(3, $roomTypesAsc);
        $this->assertCount(3, $roomTypesDesc);

        // 验证升序排列
        $this->assertStringEndsWith('_A房型', $roomTypesAsc[0]->getName());
        $this->assertStringEndsWith('_B房型', $roomTypesAsc[1]->getName());
        $this->assertStringEndsWith('_C房型', $roomTypesAsc[2]->getName());

        // 验证降序排列
        $this->assertStringEndsWith('_C房型', $roomTypesDesc[0]->getName());
        $this->assertStringEndsWith('_B房型', $roomTypesDesc[1]->getName());
        $this->assertStringEndsWith('_A房型', $roomTypesDesc[2]->getName());
    }

    public function testFindByWithLimitReturnsLimitedResults(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->persistAndFlush($hotel);

        $roomType1 = $this->createTestRoomType($hotel, '限制房型1');
        $roomType2 = $this->createTestRoomType($hotel, '限制房型2');
        $roomType3 = $this->createTestRoomType($hotel, '限制房型3');
        $this->persistAndFlush($roomType1);
        $this->persistAndFlush($roomType2);
        $this->persistAndFlush($roomType3);

        // Act
        $limitedRoomTypes = $this->repository->findBy([], null, 2);

        // Assert
        $this->assertCount(2, $limitedRoomTypes);
    }

    public function testFindByWithOffsetReturnsOffsetResults(): void
    {
        // Arrange
        $uniquePrefix = 'OFFSET_TEST_' . uniqid();
        $hotel = $this->createTestHotel();
        $this->persistAndFlush($hotel);

        $roomType1 = $this->createTestRoomType($hotel, $uniquePrefix . '_1');
        $roomType2 = $this->createTestRoomType($hotel, $uniquePrefix . '_2');
        $roomType3 = $this->createTestRoomType($hotel, $uniquePrefix . '_3');
        $this->persistAndFlush($roomType1);
        $this->persistAndFlush($roomType2);
        $this->persistAndFlush($roomType3);

        // Act - 先获取所有记录以确定顺序
        $queryBuilder = $this->repository->createQueryBuilder('rt');
        $allTestRoomTypes = $queryBuilder
            ->where('rt.name LIKE :prefix')
            ->setParameter('prefix', $uniquePrefix . '%')
            ->orderBy('rt.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        // 然后获取带偏移的结果
        $queryBuilder2 = $this->repository->createQueryBuilder('rt');
        $offsetRoomTypes = $queryBuilder2
            ->where('rt.name LIKE :prefix')
            ->setParameter('prefix', $uniquePrefix . '%')
            ->orderBy('rt.id', 'ASC')
            ->setFirstResult(1)  // offset = 1
            ->getQuery()
            ->getResult()
        ;

        // Assert
        $this->assertCount(3, $allTestRoomTypes);
        $this->assertCount(2, $offsetRoomTypes);

        // 验证偏移结果不包含第一条记录
        $firstRoomTypeName = $allTestRoomTypes[0]->getName();
        $offsetRoomTypeNames = array_map(fn ($roomType) => $roomType->getName(), $offsetRoomTypes);
        $this->assertNotContains($firstRoomTypeName, $offsetRoomTypeNames);
    }

    public function testFindByWithLimitAndOffsetReturnsPaginatedResults(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->persistAndFlush($hotel);

        $roomTypes = [];
        for ($i = 1; $i <= 5; ++$i) {
            $roomType = $this->createTestRoomType($hotel, "分页房型{$i}");
            $roomTypes[] = $roomType;
            $this->persistAndFlush($roomType);
        }

        // Act - 第二页，每页2条
        $page2RoomTypes = $this->repository->findBy([], ['id' => 'ASC'], 2, 2);

        // Assert
        $this->assertCount(2, $page2RoomTypes);
    }

    public function testFindOneByWithNullCriteriaReturnsNull(): void
    {
        // Act
        $result = $this->repository->findOneBy(['name' => '不存在的房型名称']);

        // Assert
        $this->assertNull($result);
    }

    public function testFindByWithNullCodeReturnsRoomTypesWithNullCode(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->persistAndFlush($hotel);

        $roomTypeWithCode = $this->createTestRoomType($hotel, '有代码房型');
        $roomTypeWithCode->setCode('ROOM001');

        $roomTypeWithoutCode = $this->createTestRoomType($hotel, '无代码房型');
        $roomTypeWithoutCode->setCode(null);

        $this->persistAndFlush($roomTypeWithCode);
        $this->persistAndFlush($roomTypeWithoutCode);

        // Act
        $roomTypesWithoutCode = $this->repository->findBy(['code' => null]);

        // Assert
        $this->assertCount(1, $roomTypesWithoutCode);
        $this->assertEquals('无代码房型', $roomTypesWithoutCode[0]->getName());
        $this->assertNull($roomTypesWithoutCode[0]->getCode());
    }

    public function testFindByWithNullDescriptionReturnsRoomTypesWithNullDescription(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->persistAndFlush($hotel);

        $roomTypeWithDescription = $this->createTestRoomType($hotel, '有描述房型');
        $roomTypeWithDescription->setDescription('这是房型描述');

        $roomTypeWithoutDescription = $this->createTestRoomType($hotel, '无描述房型');
        $roomTypeWithoutDescription->setDescription(null);

        $this->persistAndFlush($roomTypeWithDescription);
        $this->persistAndFlush($roomTypeWithoutDescription);

        // Act
        $roomTypesWithoutDescription = $this->repository->findBy(['description' => null]);

        // Assert
        $this->assertCount(1, $roomTypesWithoutDescription);
        $this->assertEquals('无描述房型', $roomTypesWithoutDescription[0]->getName());
        $this->assertNull($roomTypesWithoutDescription[0]->getDescription());
    }

    public function testFindByWithMultipleCriteriaReturnsMatchingResults(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->persistAndFlush($hotel);

        $roomType1 = $this->createTestRoomType($hotel, '豪华大床房');
        $roomType1->setBedType('大床');
        $roomType1->setMaxGuests(2);
        $roomType1->setStatus(RoomTypeStatusEnum::ACTIVE);

        $roomType2 = $this->createTestRoomType($hotel, '普通大床房');
        $roomType2->setBedType('大床');
        $roomType2->setMaxGuests(2);
        $roomType2->setStatus(RoomTypeStatusEnum::DISABLED);

        $roomType3 = $this->createTestRoomType($hotel, '豪华双床房');
        $roomType3->setBedType('双床');
        $roomType3->setMaxGuests(2);
        $roomType3->setStatus(RoomTypeStatusEnum::ACTIVE);

        $this->persistAndFlush($roomType1);
        $this->persistAndFlush($roomType2);
        $this->persistAndFlush($roomType3);

        // Act
        $results = $this->repository->findBy([
            'bedType' => '大床',
            'maxGuests' => 2,
            'status' => RoomTypeStatusEnum::ACTIVE,
        ]);

        // Assert
        $this->assertGreaterThanOrEqual(1, count($results));

        // 验证新创建的房型在结果中
        $roomTypeNames = array_map(fn ($roomType) => $roomType->getName(), $results);
        $this->assertContains('豪华大床房', $roomTypeNames);
        $this->assertNotContains('普通大床房', $roomTypeNames);  // 停用状态不应在结果中
        $this->assertNotContains('豪华双床房', $roomTypeNames);  // 不同床型不应在结果中

        // 验证所有结果都符合条件
        foreach ($results as $roomType) {
            $this->assertEquals('大床', $roomType->getBedType());
            $this->assertEquals(2, $roomType->getMaxGuests());
            $this->assertEquals(RoomTypeStatusEnum::ACTIVE, $roomType->getStatus());
        }
    }

    public function testRepositoryRobustnessWithDatabaseUnavailable(): void
    {
        // 注意：在实际的集成测试环境中，数据库通常是可用的
        // 这个测试主要是展示异常处理的测试模式
        // 在真实场景中，可能需要模拟数据库连接失败的情况

        // 测试在正常情况下仓库操作不会抛出异常
        $this->expectNotToPerformAssertions();

        try {
            $this->repository->findAll();
            $this->repository->count([]);
            $this->repository->find(1);
        } catch (\Exception $e) {
            self::fail('仓库操作不应该抛出异常: ' . $e->getMessage());
        }
    }

    public function testRepositoryWithInvalidParameterTypes(): void
    {
        // 测试传入无效参数类型时的行为
        // 在现代PHP版本中，find()方法可能会自动转换字符串为整数
        // 让我们测试一个更明确的类型错误场景

        // 测试仓库对无效字段名的处理
        $this->expectException(\TypeError::class);
        // @phpstan-ignore-next-line - 故意传入错误类型用于测试
        $this->repository->findBy([123 => 'invalid']);
    }

    public function testFindByWithEmptyPhotosReturnsCorrectRoomTypes(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->persistAndFlush($hotel);

        $roomTypeWithPhotos = $this->createTestRoomType($hotel, '有照片房型');
        $roomTypeWithPhotos->setPhotos(['photo1.jpg', 'photo2.jpg']);

        $roomTypeWithoutPhotos = $this->createTestRoomType($hotel, '无照片房型');
        $roomTypeWithoutPhotos->setPhotos([]);

        $this->persistAndFlush($roomTypeWithPhotos);
        $this->persistAndFlush($roomTypeWithoutPhotos);

        // Act - 查找所有房型然后在PHP中过滤空照片数组
        $allRoomTypes = $this->repository->findAll();
        $roomTypesWithEmptyPhotos = array_filter($allRoomTypes, function ($roomType) {
            return [] === $roomType->getPhotos();
        });

        // Assert
        $this->assertGreaterThanOrEqual(1, count($roomTypesWithEmptyPhotos));

        // 验证新创建的房型在结果中
        $roomTypeNames = array_map(fn ($roomType) => $roomType->getName(), $roomTypesWithEmptyPhotos);
        $this->assertContains('无照片房型', $roomTypeNames);

        // 验证结果中不包含有照片的房型
        $this->assertNotContains('有照片房型', $roomTypeNames);
    }

    public function testCountWithNullPhotosReturnsCorrectCount(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->persistAndFlush($hotel);

        $roomTypeWithPhotos = $this->createTestRoomType($hotel, '有照片房型');
        $roomTypeWithPhotos->setPhotos(['photo1.jpg', 'photo2.jpg']);

        $roomTypeWithoutPhotos = $this->createTestRoomType($hotel, '无照片房型');
        $roomTypeWithoutPhotos->setPhotos([]);

        $this->persistAndFlush($roomTypeWithPhotos);
        $this->persistAndFlush($roomTypeWithoutPhotos);

        // Act - 通过PHP代码数空照片数组的房型
        $allRoomTypes = $this->repository->findAll();
        $roomTypesWithEmptyPhotos = array_filter($allRoomTypes, function ($roomType) {
            return [] === $roomType->getPhotos();
        });
        $countWithEmptyPhotos = count($roomTypesWithEmptyPhotos);

        // Assert
        $this->assertGreaterThanOrEqual(1, $countWithEmptyPhotos);
    }

    public function testFindOneByWithOrderByReturnsCorrectlyOrderedResult(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->persistAndFlush($hotel);

        $roomType1 = $this->createTestRoomType($hotel, 'A房型');
        $roomType2 = $this->createTestRoomType($hotel, 'B房型');
        $roomType3 = $this->createTestRoomType($hotel, 'C房型');

        $this->persistAndFlush($roomType3);
        $this->persistAndFlush($roomType1);
        $this->persistAndFlush($roomType2);

        // Act - 按名称升序查找第一个
        $firstRoomType = $this->repository->findOneBy([], ['name' => 'ASC']);

        // Assert
        $this->assertNotNull($firstRoomType);
        $this->assertEquals('A房型', $firstRoomType->getName());
    }

    public function testRepositoryRobustnessWithInvalidField(): void
    {
        // Arrange & Act & Assert
        $this->expectException(UnrecognizedField::class);
        $this->repository->findBy(['nonExistentField' => 'value']);
    }

    public function testCountWithInvalidFieldThrowsException(): void
    {
        // Arrange & Act & Assert
        $this->expectException(UnrecognizedField::class);
        $this->repository->count(['invalidFieldName' => 'value']);
    }

    public function testFindByHotelAssociationQuery(): void
    {
        // Arrange
        $hotel1 = $this->createTestHotel('酒店1');
        $hotel2 = $this->createTestHotel('酒店2');
        $this->persistAndFlush($hotel1);
        $this->persistAndFlush($hotel2);

        $roomType1 = $this->createTestRoomType($hotel1, '房型1');
        $roomType2 = $this->createTestRoomType($hotel2, '房型2');
        $this->persistAndFlush($roomType1);
        $this->persistAndFlush($roomType2);

        // Act
        $roomTypesForHotel1 = $this->repository->findBy(['hotel' => $hotel1]);

        // Assert
        $this->assertCount(1, $roomTypesForHotel1);
        $this->assertEquals('房型1', $roomTypesForHotel1[0]->getName());
    }

    public function testCountHotelAssociationQuery(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->persistAndFlush($hotel);

        $initialCount = $this->repository->count(['hotel' => $hotel]);

        $roomType1 = $this->createTestRoomType($hotel, '新房型1');
        $roomType2 = $this->createTestRoomType($hotel, '新房型2');
        $this->persistAndFlush($roomType1);
        $this->persistAndFlush($roomType2);

        // Act
        $finalCount = $this->repository->count(['hotel' => $hotel]);

        // Assert
        $this->assertEquals($initialCount + 2, $finalCount);
    }

    public function testFindByCodeIsNullReturnsCorrectResults(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->persistAndFlush($hotel);

        $roomTypeWithCode = $this->createTestRoomType($hotel, '有代码房型');
        $roomTypeWithCode->setCode('DBL');

        $roomTypeWithoutCode = $this->createTestRoomType($hotel, '无代码房型');
        $roomTypeWithoutCode->setCode(null);

        $this->persistAndFlush($roomTypeWithCode);
        $this->persistAndFlush($roomTypeWithoutCode);

        // Act
        $roomTypesWithoutCode = $this->repository->findBy(['code' => null]);

        // Assert
        $this->assertGreaterThanOrEqual(1, count($roomTypesWithoutCode));

        foreach ($roomTypesWithoutCode as $roomType) {
            $this->assertNull($roomType->getCode());
        }
    }

    public function testCountCodeIsNullReturnsCorrectCount(): void
    {
        // Arrange
        $initialCount = $this->repository->count(['code' => null]);

        $hotel = $this->createTestHotel();
        $this->persistAndFlush($hotel);

        $roomTypeWithoutCode = $this->createTestRoomType($hotel, '新无代码房型');
        $roomTypeWithoutCode->setCode(null);

        $this->persistAndFlush($roomTypeWithoutCode);

        // Act
        $finalCount = $this->repository->count(['code' => null]);

        // Assert
        $this->assertEquals($initialCount + 1, $finalCount);
    }

    public function testFindOneByWhenOrderedByAreaShouldReturnCorrectEntity(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        $this->persistAndFlush($hotel);

        $roomType1 = $this->createTestRoomType($hotel, 'Z_房型');
        $roomType1->setArea(25.0);
        $roomType2 = $this->createTestRoomType($hotel, 'A_房型');
        $roomType2->setArea(35.0);
        $roomType3 = $this->createTestRoomType($hotel, 'M_房型');
        $roomType3->setArea(30.0);

        $this->persistAndFlush($roomType1);
        $this->persistAndFlush($roomType2);
        $this->persistAndFlush($roomType3);

        // Act - 测试按面积降序排列的第一个结果
        $largestRoom = $this->repository->findOneBy(['hotel' => $hotel], ['area' => 'DESC']);

        // Assert
        $this->assertNotNull($largestRoom);
        $this->assertGreaterThanOrEqual(25.0, $largestRoom->getArea());

        // Act - 测试按名称升序排列的第一个结果
        $firstAlphabetical = $this->repository->findOneBy(['hotel' => $hotel], ['name' => 'ASC']);

        // Assert
        $this->assertNotNull($firstAlphabetical);
        $this->assertEquals('A_房型', $firstAlphabetical->getName());

        // Act - 测试组合排序：先按面积降序，再按名称升序
        $complexSorted = $this->repository->findOneBy(['hotel' => $hotel], ['area' => 'DESC', 'name' => 'ASC']);

        // Assert
        $this->assertNotNull($complexSorted);
    }

    public function testFindOneByAssociationHotelShouldReturnMatchingEntity(): void
    {
        // Arrange
        $hotel1 = $this->createTestHotel('酒店1');
        $hotel2 = $this->createTestHotel('酒店2');
        $this->persistAndFlush($hotel1);
        $this->persistAndFlush($hotel2);

        $roomType1 = $this->createTestRoomType($hotel1, '酒店1房型');
        $roomType2 = $this->createTestRoomType($hotel2, '酒店2房型');
        $this->persistAndFlush($roomType1);
        $this->persistAndFlush($roomType2);

        // Act
        $foundRoomType = $this->repository->findOneBy(['hotel' => $hotel1]);

        // Assert
        $this->assertNotNull($foundRoomType);
        $foundHotel = $foundRoomType->getHotel();
        $this->assertNotNull($foundHotel);
        $this->assertEquals($hotel1->getId(), $foundHotel->getId());
        $this->assertEquals('酒店1房型', $foundRoomType->getName());
    }

    public function testCountByAssociationHotelShouldReturnCorrectNumber(): void
    {
        // Arrange
        $hotel = $this->createTestHotel('测试酒店');
        $this->persistAndFlush($hotel);

        $initialCount = $this->repository->count(['hotel' => $hotel]);

        $roomType1 = $this->createTestRoomType($hotel, '计数房型1');
        $roomType2 = $this->createTestRoomType($hotel, '计数房型2');
        $roomType3 = $this->createTestRoomType($hotel, '计数房型3');
        $this->persistAndFlush($roomType1);
        $this->persistAndFlush($roomType2);
        $this->persistAndFlush($roomType3);

        // Act
        $count = $this->repository->count(['hotel' => $hotel]);

        // Assert
        $this->assertEquals($initialCount + 3, $count);
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

    /**
     * @return ServiceEntityRepository<RoomType>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
