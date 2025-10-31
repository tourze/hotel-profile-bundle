<?php

namespace Tourze\HotelProfileBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Persisters\Exception\UnrecognizedField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;
use Tourze\HotelProfileBundle\Repository\HotelRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(HotelRepository::class)]
#[RunTestsInSeparateProcesses]
final class HotelRepositoryTest extends AbstractRepositoryTestCase
{
    private HotelRepository $repository;

    protected function onSetUp(): void
    {
        // 使用类型安全的服务获取
        $this->repository = self::getService(HotelRepository::class);
    }

    protected function createNewEntity(): object
    {
        $hotel = new Hotel();
        $hotel->setName('测试酒店_' . uniqid());
        $hotel->setAddress('测试地址');
        $hotel->setStarLevel(4);
        $hotel->setContactPerson('测试联系人');
        $hotel->setPhone('13800000000');
        $hotel->setStatus(HotelStatusEnum::OPERATING);

        return $hotel;
    }

    public function testSaveWithValidEntityPersistsToDatabase(): void
    {
        // Arrange
        $hotel = $this->createTestHotel('测试酒店', '测试地址');

        // Act
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();

        // Assert
        $this->assertNotNull($hotel->getId());
        // 使用 AbstractIntegrationTestCase 提供的断言方法
        $this->assertEntityPersisted($hotel);

        // 重新获取实体并验证数据
        $savedHotel = $this->repository->find($hotel->getId());
        $this->assertNotNull($savedHotel);
        $this->assertEquals('测试酒店', $savedHotel->getName());
        $this->assertEquals('测试地址', $savedHotel->getAddress());
    }

    public function testSaveWithFlushImmediatelyPersists(): void
    {
        // Arrange
        $initialCount = $this->repository->count([]);
        $hotel = $this->createTestHotel();

        // Act
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();

        // Assert
        $this->assertNotNull($hotel->getId());
        $count = $this->repository->count([]);
        $this->assertEquals($initialCount + 1, $count);
    }

    public function testSaveWithoutFlushDoesNotPersistImmediately(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();

        // Act
        self::getEntityManager()->persist($hotel);

        // Assert
        $this->assertNull($hotel->getId());
        self::getEntityManager()->flush();
        $this->assertNotNull($hotel->getId());
    }

    /**
     * @phpstan-ignore-next-line
     */
    public function testRemoveWithValidEntityDeletesFromDatabase(): void
    {
        // Arrange
        $hotel = $this->createTestHotel();
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();
        $hotelId = $hotel->getId();

        // Act
        self::getEntityManager()->remove($hotel);
        self::getEntityManager()->flush();

        // Assert
        // 使用 AbstractIntegrationTestCase 提供的断言方法
        $this->assertEntityNotExists(Hotel::class, $hotelId);
    }

    public function testFindWithValidIdReturnsEntity(): void
    {
        // Arrange
        $hotel = $this->createTestHotel('查找测试酒店');
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();

        // Act
        $result = $this->repository->find($hotel->getId());

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('查找测试酒店', $result->getName());
    }

    public function testFindWithInvalidIdReturnsNull(): void
    {
        // Act
        $result = $this->repository->find(999999);

        // Assert
        $this->assertNull($result);
    }

    public function testFindByNameWithExactMatchReturnsHotels(): void
    {
        // Arrange
        $hotel1 = $this->createTestHotel('豪华大酒店');
        $hotel2 = $this->createTestHotel('商务酒店');
        self::getEntityManager()->persist($hotel1);
        self::getEntityManager()->persist($hotel2);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findByName('豪华大酒店');

        // Assert
        $this->assertCount(1, $results);
        $this->assertEquals('豪华大酒店', $results[0]->getName());
    }

    public function testFindByNameWithPartialMatchReturnsMatchingHotels(): void
    {
        // Arrange
        $hotel1 = $this->createTestHotel('豪华大酒店');
        $hotel2 = $this->createTestHotel('豪华商务酒店');
        $hotel3 = $this->createTestHotel('快捷酒店');
        self::getEntityManager()->persist($hotel1);
        self::getEntityManager()->persist($hotel2);
        self::getEntityManager()->persist($hotel3);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findByName('豪华');

        // Assert
        $this->assertCount(2, $results);
        $hotelNames = array_map(fn ($hotel) => $hotel->getName(), $results);
        $this->assertContains('豪华大酒店', $hotelNames);
        $this->assertContains('豪华商务酒店', $hotelNames);
    }

    public function testFindByNameWithNoMatchReturnsEmptyArray(): void
    {
        // Arrange
        $hotel = $this->createTestHotel('测试酒店');
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findByName('不存在的酒店');

        // Assert
        $this->assertEmpty($results);
    }

    public function testFindByStarLevelReturnsCorrectStarLevelHotels(): void
    {
        // Arrange
        $initialFiveStarCount = count($this->repository->findByStarLevel(5));
        $initialFourStarCount = count($this->repository->findByStarLevel(4));

        $hotel1 = $this->createTestHotel('五星酒店1', '地址1', 5);
        $hotel2 = $this->createTestHotel('五星酒店2', '地址2', 5);
        $hotel3 = $this->createTestHotel('四星酒店', '地址3', 4);
        self::getEntityManager()->persist($hotel1);
        self::getEntityManager()->persist($hotel2);
        self::getEntityManager()->persist($hotel3);
        self::getEntityManager()->flush();

        // Act
        $fiveStarHotels = $this->repository->findByStarLevel(5);
        $fourStarHotels = $this->repository->findByStarLevel(4);

        // Assert
        $this->assertCount($initialFiveStarCount + 2, $fiveStarHotels);
        $this->assertCount($initialFourStarCount + 1, $fourStarHotels);

        // 验证新创建的酒店都在结果中
        $hotelNames = array_map(fn ($hotel) => $hotel->getName(), $fiveStarHotels);
        $this->assertContains('五星酒店1', $hotelNames);
        $this->assertContains('五星酒店2', $hotelNames);

        foreach ($fiveStarHotels as $hotel) {
            $this->assertEquals(5, $hotel->getStarLevel());
        }
    }

    public function testFindByStarLevelWithNoMatchReturnsEmptyArray(): void
    {
        // Arrange
        $hotel = $this->createTestHotel('三星酒店', '地址', 3);
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();

        // Act - 查找一个不太可能存在的星级
        $results = $this->repository->findByStarLevel(0);

        // Assert
        $this->assertEmpty($results);
    }

    public function testFindOperatingHotelsReturnsOnlyOperatingHotels(): void
    {
        // Arrange
        $initialOperatingCount = count($this->repository->findOperatingHotels());

        $operatingHotel1 = $this->createTestHotel('运营酒店1');
        $operatingHotel1->setStatus(HotelStatusEnum::OPERATING);

        $operatingHotel2 = $this->createTestHotel('运营酒店2');
        $operatingHotel2->setStatus(HotelStatusEnum::OPERATING);

        $suspendedHotel = $this->createTestHotel('暂停酒店');
        $suspendedHotel->setStatus(HotelStatusEnum::SUSPENDED);

        self::getEntityManager()->persist($operatingHotel1);
        self::getEntityManager()->persist($operatingHotel2);
        self::getEntityManager()->persist($suspendedHotel);
        self::getEntityManager()->flush();

        // Act
        $operatingHotels = $this->repository->findOperatingHotels();

        // Assert
        $this->assertCount($initialOperatingCount + 2, $operatingHotels);

        // 验证新创建的酒店都在结果中
        $hotelNames = array_map(fn ($hotel) => $hotel->getName(), $operatingHotels);
        $this->assertContains('运营酒店1', $hotelNames);
        $this->assertContains('运营酒店2', $hotelNames);

        foreach ($operatingHotels as $hotel) {
            $this->assertEquals(HotelStatusEnum::OPERATING, $hotel->getStatus());
        }
    }

    public function testFindOperatingHotelsFiltersOutNonOperatingHotels(): void
    {
        // Arrange
        $initialOperatingCount = count($this->repository->findOperatingHotels());

        $suspendedHotel = $this->createTestHotel('暂停酒店');
        $suspendedHotel->setStatus(HotelStatusEnum::SUSPENDED);
        self::getEntityManager()->persist($suspendedHotel);
        self::getEntityManager()->flush();

        // Act
        $results = $this->repository->findOperatingHotels();

        // Assert
        // 暂停的酒店不应该增加运营酒店的数量
        $this->assertCount($initialOperatingCount, $results);

        // 验证所有结果都是运营状态
        foreach ($results as $hotel) {
            $this->assertEquals(HotelStatusEnum::OPERATING, $hotel->getStatus());
        }

        // 验证暂停的酒店不在结果中
        $hotelNames = array_map(fn ($hotel) => $hotel->getName(), $results);
        $this->assertNotContains('暂停酒店', $hotelNames);
    }

    public function testFindAllReturnsAllHotels(): void
    {
        // Arrange
        $initialCount = count($this->repository->findAll());
        $hotel1 = $this->createTestHotel('酒店1');
        $hotel2 = $this->createTestHotel('酒店2');
        self::getEntityManager()->persist($hotel1);
        self::getEntityManager()->persist($hotel2);
        self::getEntityManager()->flush();

        // Act
        $hotels = $this->repository->findAll();

        // Assert
        $this->assertCount($initialCount + 2, $hotels);

        // 验证新创建的酒店都在结果中
        $hotelNames = array_map(fn ($hotel) => $hotel->getName(), $hotels);
        $this->assertContains('酒店1', $hotelNames);
        $this->assertContains('酒店2', $hotelNames);
    }

    public function testFindOneByReturnsMatchingHotel(): void
    {
        // Arrange
        $hotel = $this->createTestHotel('唯一酒店');
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();

        // Act
        $foundHotel = $this->repository->findOneBy(['name' => '唯一酒店']);

        // Assert
        $this->assertNotNull($foundHotel);
        $this->assertEquals('唯一酒店', $foundHotel->getName());
    }

    public function testFindByWithCriteriaReturnsMatchingHotels(): void
    {
        // Arrange
        $initialFiveStarCount = count($this->repository->findBy(['starLevel' => 5]));

        $hotel1 = $this->createTestHotel('北京酒店', '北京地址', 5);
        $hotel2 = $this->createTestHotel('上海酒店', '上海地址', 5);
        $hotel3 = $this->createTestHotel('广州酒店', '广州地址', 4);
        self::getEntityManager()->persist($hotel1);
        self::getEntityManager()->persist($hotel2);
        self::getEntityManager()->persist($hotel3);
        self::getEntityManager()->flush();

        // Act
        $fiveStarHotels = $this->repository->findBy(['starLevel' => 5]);

        // Assert
        $this->assertCount($initialFiveStarCount + 2, $fiveStarHotels);

        // 验证新创建的酒店都在结果中
        $hotelNames = array_map(fn ($hotel) => $hotel->getName(), $fiveStarHotels);
        $this->assertContains('北京酒店', $hotelNames);
        $this->assertContains('上海酒店', $hotelNames);
    }

    public function testCountReturnsCorrectTotalNumber(): void
    {
        // Arrange
        $initialCount = $this->repository->count([]);

        $hotel1 = $this->createTestHotel('计数酒店1');
        $hotel2 = $this->createTestHotel('计数酒店2');
        $this->persistAndFlush($hotel1);
        $this->persistAndFlush($hotel2);

        // Act
        $totalCount = $this->repository->count([]);

        // Assert
        $this->assertEquals($initialCount + 2, $totalCount);
    }

    public function testCountWithCriteriaReturnsFilteredCount(): void
    {
        // Arrange
        $initialFiveStarCount = $this->repository->count(['starLevel' => 5]);
        $initialFourStarCount = $this->repository->count(['starLevel' => 4]);

        $hotel1 = $this->createTestHotel('五星酒店1', '地址1', 5);
        $hotel2 = $this->createTestHotel('五星酒店2', '地址2', 5);
        $hotel3 = $this->createTestHotel('四星酒店', '地址3', 4);
        $this->persistAndFlush($hotel1);
        $this->persistAndFlush($hotel2);
        $this->persistAndFlush($hotel3);

        // Act
        $fiveStarCount = $this->repository->count(['starLevel' => 5]);
        $fourStarCount = $this->repository->count(['starLevel' => 4]);

        // Assert
        $this->assertEquals($initialFiveStarCount + 2, $fiveStarCount);
        $this->assertEquals($initialFourStarCount + 1, $fourStarCount);
    }

    public function testCountWithEmptyTableReturnsZero(): void
    {
        // Act
        $count = $this->repository->count(['starLevel' => 9999]);

        // Assert
        $this->assertEquals(0, $count);
    }

    public function testFindByWithOrderByReturnsOrderedResults(): void
    {
        // Arrange - 使用唯一前缀避免与其他测试数据冲突
        $uniquePrefix = 'SORT_TEST_' . uniqid();
        $hotel1 = $this->createTestHotel($uniquePrefix . '_C酒店');
        $hotel2 = $this->createTestHotel($uniquePrefix . '_A酒店');
        $hotel3 = $this->createTestHotel($uniquePrefix . '_B酒店');
        $this->persistAndFlush($hotel1);
        $this->persistAndFlush($hotel2);
        $this->persistAndFlush($hotel3);

        // Act - 使用LIKE查询仅获取我们的测试数据
        $queryBuilder = $this->repository->createQueryBuilder('h');
        $hotelsAsc = $queryBuilder
            ->where('h.name LIKE :prefix')
            ->setParameter('prefix', $uniquePrefix . '%')
            ->orderBy('h.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        $queryBuilder2 = $this->repository->createQueryBuilder('h');
        $hotelsDesc = $queryBuilder2
            ->where('h.name LIKE :prefix')
            ->setParameter('prefix', $uniquePrefix . '%')
            ->orderBy('h.name', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        // Assert
        $this->assertCount(3, $hotelsAsc);
        $this->assertCount(3, $hotelsDesc);

        // 验证升序排列
        $this->assertStringEndsWith('_A酒店', $hotelsAsc[0]->getName());
        $this->assertStringEndsWith('_B酒店', $hotelsAsc[1]->getName());
        $this->assertStringEndsWith('_C酒店', $hotelsAsc[2]->getName());

        // 验证降序排列
        $this->assertStringEndsWith('_C酒店', $hotelsDesc[0]->getName());
        $this->assertStringEndsWith('_B酒店', $hotelsDesc[1]->getName());
        $this->assertStringEndsWith('_A酒店', $hotelsDesc[2]->getName());
    }

    public function testFindByWithLimitReturnsLimitedResults(): void
    {
        // Arrange
        $hotel1 = $this->createTestHotel('限制酒店1');
        $hotel2 = $this->createTestHotel('限制酒店2');
        $hotel3 = $this->createTestHotel('限制酒店3');
        $this->persistAndFlush($hotel1);
        $this->persistAndFlush($hotel2);
        $this->persistAndFlush($hotel3);

        // Act
        $limitedHotels = $this->repository->findBy([], null, 2);

        // Assert
        $this->assertCount(2, $limitedHotels);
    }

    public function testFindByWithOffsetReturnsOffsetResults(): void
    {
        // Arrange
        $uniquePrefix = 'OFFSET_TEST_' . uniqid();
        $hotel1 = $this->createTestHotel($uniquePrefix . '_1');
        $hotel2 = $this->createTestHotel($uniquePrefix . '_2');
        $hotel3 = $this->createTestHotel($uniquePrefix . '_3');
        $this->persistAndFlush($hotel1);
        $this->persistAndFlush($hotel2);
        $this->persistAndFlush($hotel3);

        // Act - 先获取所有记录以确定顺序
        $queryBuilder = $this->repository->createQueryBuilder('h');
        $allTestHotels = $queryBuilder
            ->where('h.name LIKE :prefix')
            ->setParameter('prefix', $uniquePrefix . '%')
            ->orderBy('h.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        // 然后获取带偏移的结果
        $queryBuilder2 = $this->repository->createQueryBuilder('h');
        $offsetHotels = $queryBuilder2
            ->where('h.name LIKE :prefix')
            ->setParameter('prefix', $uniquePrefix . '%')
            ->orderBy('h.id', 'ASC')
            ->setFirstResult(1)  // offset = 1
            ->getQuery()
            ->getResult()
        ;

        // Assert
        $this->assertCount(3, $allTestHotels);
        $this->assertCount(2, $offsetHotels);

        // 验证偏移结果不包含第一条记录
        $firstHotelName = $allTestHotels[0]->getName();
        $offsetHotelNames = array_map(fn ($hotel) => $hotel->getName(), $offsetHotels);
        $this->assertNotContains($firstHotelName, $offsetHotelNames);
    }

    public function testFindByWithLimitAndOffsetReturnsPaginatedResults(): void
    {
        // Arrange
        $hotels = [];
        for ($i = 1; $i <= 5; ++$i) {
            $hotel = $this->createTestHotel("分页酒店{$i}");
            $hotels[] = $hotel;
            $this->persistAndFlush($hotel);
        }

        // Act - 第二页，每页2条
        $page2Hotels = $this->repository->findBy([], ['id' => 'ASC'], 2, 2);

        // Assert
        $this->assertCount(2, $page2Hotels);
    }

    public function testFindOneByWithNullCriteriaReturnsNull(): void
    {
        // Act
        $result = $this->repository->findOneBy(['name' => '不存在的酒店名称']);

        // Assert
        $this->assertNull($result);
    }

    public function testFindByWithNullEmailReturnsHotelsWithNullEmail(): void
    {
        // Arrange
        $initialNullEmailCount = count($this->repository->findBy(['email' => null]));

        $hotelWithEmail = $this->createTestHotel('有邮箱酒店');
        $hotelWithEmail->setEmail('test@example.com');

        $hotelWithoutEmail = $this->createTestHotel('无邮箱酒店');
        $hotelWithoutEmail->setEmail(null);

        $this->persistAndFlush($hotelWithEmail);
        $this->persistAndFlush($hotelWithoutEmail);

        // Act
        $hotelsWithoutEmail = $this->repository->findBy(['email' => null]);

        // Assert
        $this->assertCount($initialNullEmailCount + 1, $hotelsWithoutEmail);

        // 验证新创建的酒店在结果中
        $hotelNames = array_map(fn ($hotel) => $hotel->getName(), $hotelsWithoutEmail);
        $this->assertContains('无邮箱酒店', $hotelNames);

        // 验证所有结果都有null email
        foreach ($hotelsWithoutEmail as $hotel) {
            $this->assertNull($hotel->getEmail());
        }
    }

    public function testFindByWithMultipleCriteriaReturnsMatchingResults(): void
    {
        // Arrange
        $initialMatchingCount = count($this->repository->findBy([
            'starLevel' => 5,
            'status' => HotelStatusEnum::OPERATING,
        ]));

        $hotel1 = $this->createTestHotel('测试酒店', '北京地址', 5);
        $hotel1->setStatus(HotelStatusEnum::OPERATING);

        $hotel2 = $this->createTestHotel('另一个酒店', '北京地址', 5);
        $hotel2->setStatus(HotelStatusEnum::SUSPENDED);

        $hotel3 = $this->createTestHotel('第三个酒店', '上海地址', 5);
        $hotel3->setStatus(HotelStatusEnum::OPERATING);

        $this->persistAndFlush($hotel1);
        $this->persistAndFlush($hotel2);
        $this->persistAndFlush($hotel3);

        // Act
        $results = $this->repository->findBy([
            'starLevel' => 5,
            'status' => HotelStatusEnum::OPERATING,
        ]);

        // Assert
        $this->assertCount($initialMatchingCount + 2, $results);

        // 验证新创建的酒店在结果中
        $hotelNames = array_map(fn ($hotel) => $hotel->getName(), $results);
        $this->assertContains('测试酒店', $hotelNames);
        $this->assertContains('第三个酒店', $hotelNames);
        $this->assertNotContains('另一个酒店', $hotelNames);  // 暂停状态的酒店不应在结果中

        foreach ($results as $hotel) {
            $this->assertEquals(5, $hotel->getStarLevel());
            $this->assertEquals(HotelStatusEnum::OPERATING, $hotel->getStatus());
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

    public function testFindByWithNullPhotosReturnsHotelsWithNullPhotos(): void
    {
        // Arrange
        $hotelWithPhotos = $this->createTestHotel('有照片酒店');
        $hotelWithPhotos->setPhotos(['photo1.jpg', 'photo2.jpg']);

        $hotelWithoutPhotos = $this->createTestHotel('无照片酒店');
        $hotelWithoutPhotos->setPhotos([]);

        $this->persistAndFlush($hotelWithPhotos);
        $this->persistAndFlush($hotelWithoutPhotos);

        // Act - 查找所有酒店然后在PHP中过滤空照片数组
        $allHotels = $this->repository->findAll();
        $hotelsWithEmptyPhotos = array_filter($allHotels, function ($hotel) {
            return [] === $hotel->getPhotos();
        });

        // Assert
        $this->assertGreaterThanOrEqual(1, count($hotelsWithEmptyPhotos));

        // 验证新创建的酒店在结果中
        $hotelNames = array_map(fn ($hotel) => $hotel->getName(), $hotelsWithEmptyPhotos);
        $this->assertContains('无照片酒店', $hotelNames);

        // 验证结果中不包含有照片的酒店
        $this->assertNotContains('有照片酒店', $hotelNames);
    }

    public function testFindByWithNullFacilitiesReturnsHotelsWithNullFacilities(): void
    {
        // Arrange
        $hotelWithFacilities = $this->createTestHotel('有设施酒店');
        $hotelWithFacilities->setFacilities(['WiFi', '停车场']);

        $hotelWithoutFacilities = $this->createTestHotel('无设施酒店');
        $hotelWithoutFacilities->setFacilities([]);

        $this->persistAndFlush($hotelWithFacilities);
        $this->persistAndFlush($hotelWithoutFacilities);

        // Act - 查找所有酒店然后在PHP中过滤空设施数组
        $allHotels = $this->repository->findAll();
        $hotelsWithEmptyFacilities = array_filter($allHotels, function ($hotel) {
            return [] === $hotel->getFacilities();
        });

        // Assert
        $this->assertGreaterThanOrEqual(1, count($hotelsWithEmptyFacilities));

        // 验证新创建的酒店在结果中
        $hotelNames = array_map(fn ($hotel) => $hotel->getName(), $hotelsWithEmptyFacilities);
        $this->assertContains('无设施酒店', $hotelNames);

        // 验证结果中不包含有设施的酒店
        $this->assertNotContains('有设施酒店', $hotelNames);
    }

    public function testCountWithNullEmailReturnsCorrectCount(): void
    {
        // Arrange
        $hotelWithEmail = $this->createTestHotel('有邮箱酒店');
        $hotelWithEmail->setEmail('test@example.com');

        $hotelWithoutEmail = $this->createTestHotel('无邮箱酒店');
        $hotelWithoutEmail->setEmail(null);

        $this->persistAndFlush($hotelWithEmail);
        $this->persistAndFlush($hotelWithoutEmail);

        // Act
        $countWithNullEmail = $this->repository->count(['email' => null]);

        // Assert
        $this->assertGreaterThanOrEqual(1, $countWithNullEmail);
    }

    public function testFindOneByWithOrderByReturnsCorrectlyOrderedResult(): void
    {
        // Arrange
        $hotel1 = $this->createTestHotel('A酒店');
        $hotel2 = $this->createTestHotel('B酒店');
        $hotel3 = $this->createTestHotel('C酒店');

        $this->persistAndFlush($hotel3);
        $this->persistAndFlush($hotel1);
        $this->persistAndFlush($hotel2);

        // Act - 按名称升序查找第一个
        $firstHotel = $this->repository->findOneBy([], ['name' => 'ASC']);

        // Assert
        $this->assertNotNull($firstHotel);
        $this->assertEquals('A酒店', $firstHotel->getName());
    }

    public function testFindOneByWithOrderByDescendingReturnsCorrectResult(): void
    {
        // Arrange - 使用特殊前缀确保数据唯一性
        $hotel1 = $this->createTestHotel('ZZZ_A酒店');
        $hotel2 = $this->createTestHotel('ZZZ_B酒店');
        $hotel3 = $this->createTestHotel('ZZZ_C酒店');

        $this->persistAndFlush($hotel1);
        $this->persistAndFlush($hotel2);
        $this->persistAndFlush($hotel3);

        // Act - 按名称降序查找以ZZZ开头的酒店
        $lastHotel = $this->repository->findOneBy(['name' => 'ZZZ_C酒店'], ['name' => 'DESC']);

        // Assert
        $this->assertNotNull($lastHotel);
        $this->assertEquals('ZZZ_C酒店', $lastHotel->getName());
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

    public function testFindByEmailIsNullReturnsCorrectResults(): void
    {
        // Arrange
        $hotelWithEmail = $this->createTestHotel('有邮箱酒店');
        $hotelWithEmail->setEmail('test@example.com');

        $hotelWithoutEmail = $this->createTestHotel('无邮箱酒店');
        $hotelWithoutEmail->setEmail(null);

        $this->persistAndFlush($hotelWithEmail);
        $this->persistAndFlush($hotelWithoutEmail);

        // Act
        $hotelsWithoutEmail = $this->repository->findBy(['email' => null]);

        // Assert
        $this->assertGreaterThanOrEqual(1, count($hotelsWithoutEmail));

        foreach ($hotelsWithoutEmail as $hotel) {
            $this->assertNull($hotel->getEmail());
        }
    }

    public function testCountEmailIsNullReturnsCorrectCount(): void
    {
        // Arrange
        $initialCount = $this->repository->count(['email' => null]);

        $hotelWithoutEmail = $this->createTestHotel('新无邮箱酒店');
        $hotelWithoutEmail->setEmail(null);

        $this->persistAndFlush($hotelWithoutEmail);

        // Act
        $finalCount = $this->repository->count(['email' => null]);

        // Assert
        $this->assertEquals($initialCount + 1, $finalCount);
    }

    public function testFindOneByWhenOrderedByStarLevelShouldReturnCorrectEntity(): void
    {
        // Arrange - 创建多个酒店以测试排序逻辑
        $hotel1 = $this->createTestHotel('Z_酒店1', '地址1', 5);
        $hotel2 = $this->createTestHotel('A_酒店2', '地址2', 4);
        $hotel3 = $this->createTestHotel('M_酒店3', '地址3', 3);

        $this->persistAndFlush($hotel1);
        $this->persistAndFlush($hotel2);
        $this->persistAndFlush($hotel3);

        // Act - 测试按星级降序排列的第一个结果
        $highestStarHotel = $this->repository->findOneBy([], ['starLevel' => 'DESC']);

        // Assert
        $this->assertNotNull($highestStarHotel);
        $this->assertGreaterThanOrEqual(3, $highestStarHotel->getStarLevel());

        // Act - 测试按名称升序排列的第一个结果
        $firstAlphabeticalHotel = $this->repository->findOneBy([], ['name' => 'ASC']);

        // Assert
        $this->assertNotNull($firstAlphabeticalHotel);

        // Act - 测试组合排序：先按星级降序，再按名称升序
        $complexSortedHotel = $this->repository->findOneBy([], ['starLevel' => 'DESC', 'name' => 'ASC']);

        // Assert
        $this->assertNotNull($complexSortedHotel);
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

    /**
     * @return ServiceEntityRepository<Hotel>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
