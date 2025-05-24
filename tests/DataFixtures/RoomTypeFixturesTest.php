<?php

namespace Tourze\HotelProfileBundle\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\HotelProfileBundle\DataFixtures\HotelFixtures;
use Tourze\HotelProfileBundle\DataFixtures\RoomTypeFixtures;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Entity\RoomType;

class RoomTypeFixturesTest extends TestCase
{
    private RoomTypeFixtures $fixtures;
    private ObjectManager&MockObject $objectManager;

    protected function setUp(): void
    {
        $this->fixtures = new RoomTypeFixtures();
        $this->objectManager = $this->createMock(ObjectManager::class);
    }

    public function test_fixture_extendsFixture(): void
    {
        $reflection = new \ReflectionClass(RoomTypeFixtures::class);
        $this->assertTrue($reflection->isSubclassOf(Fixture::class));
    }

    public function test_fixture_implementsFixtureGroupInterface(): void
    {
        $reflection = new \ReflectionClass(RoomTypeFixtures::class);
        $this->assertTrue($reflection->implementsInterface(FixtureGroupInterface::class));
    }

    public function test_fixture_implementsDependentFixtureInterface(): void
    {
        $reflection = new \ReflectionClass(RoomTypeFixtures::class);
        $this->assertTrue($reflection->implementsInterface(DependentFixtureInterface::class));
    }

    public function test_getGroups_returnsCorrectGroups(): void
    {
        $groups = RoomTypeFixtures::getGroups();
        
        $this->assertIsArray($groups);
        $this->assertContains('room', $groups);
        $this->assertContains('hotel-profile', $groups);
        $this->assertContains('default', $groups);
        $this->assertCount(3, $groups);
    }

    public function test_getDependencies_returnsHotelFixtures(): void
    {
        $dependencies = $this->fixtures->getDependencies();
        
        $this->assertIsArray($dependencies);
        $this->assertContains(HotelFixtures::class, $dependencies);
        $this->assertCount(1, $dependencies);
    }

    public function test_constants_areDefinedCorrectly(): void
    {
        $reflection = new \ReflectionClass(RoomTypeFixtures::class);
        
        $this->assertTrue($reflection->hasConstant('ROOM_TYPE_REFERENCE_PREFIX'));
        $this->assertEquals('room-type-', RoomTypeFixtures::ROOM_TYPE_REFERENCE_PREFIX);
    }

    public function test_load_methodSignature_isCorrect(): void
    {
        $reflection = new \ReflectionClass(RoomTypeFixtures::class);
        $method = $reflection->getMethod('load');
        
        $this->assertTrue($method->isPublic());
        $this->assertCount(1, $method->getParameters());
        
        $parameter = $method->getParameters()[0];
        $this->assertEquals('manager', $parameter->getName());
        $this->assertTrue($parameter->hasType());
        $this->assertEquals(ObjectManager::class, $parameter->getType()->getName());
    }

    public function test_createRoomTypesForHotel_methodExists(): void
    {
        $reflection = new \ReflectionClass(RoomTypeFixtures::class);
        $this->assertTrue($reflection->hasMethod('createRoomTypesForHotel'));
        
        $method = $reflection->getMethod('createRoomTypesForHotel');
        $this->assertTrue($method->isPrivate());
        $this->assertCount(3, $method->getParameters());
    }

    public function test_fixture_hasCorrectNamespace(): void
    {
        $reflection = new \ReflectionClass(RoomTypeFixtures::class);
        $this->assertEquals('Tourze\HotelProfileBundle\DataFixtures', $reflection->getNamespaceName());
    }

    public function test_classStructure_isCorrect(): void
    {
        $reflection = new \ReflectionClass(RoomTypeFixtures::class);
        
        // 验证类有正确的方法
        $this->assertTrue($reflection->hasMethod('load'));
        $this->assertTrue($reflection->hasMethod('getDependencies'));
        $this->assertTrue($reflection->hasMethod('getGroups'));
        $this->assertTrue($reflection->hasMethod('createRoomTypesForHotel'));
        
        // 验证类有正确的常量
        $this->assertTrue($reflection->hasConstant('ROOM_TYPE_REFERENCE_PREFIX'));
        
        // 验证方法可见性
        $loadMethod = $reflection->getMethod('load');
        $this->assertTrue($loadMethod->isPublic());
        
        $createMethod = $reflection->getMethod('createRoomTypesForHotel');
        $this->assertTrue($createMethod->isPrivate());
    }

    public function test_createRoomTypesForHotel_parameterTypes(): void
    {
        $reflection = new \ReflectionClass(RoomTypeFixtures::class);
        $method = $reflection->getMethod('createRoomTypesForHotel');
        $parameters = $method->getParameters();
        
        $this->assertCount(3, $parameters);
        
        $this->assertEquals('manager', $parameters[0]->getName());
        $this->assertEquals('hotel', $parameters[1]->getName());  
        $this->assertEquals('roomTypesData', $parameters[2]->getName());
        
        $this->assertTrue($parameters[0]->hasType());
        $this->assertTrue($parameters[1]->hasType());
        $this->assertTrue($parameters[2]->hasType());
        
        $this->assertEquals(ObjectManager::class, $parameters[0]->getType()->getName());
        $this->assertEquals(Hotel::class, $parameters[1]->getType()->getName());
        $this->assertEquals('array', $parameters[2]->getType()->getName());
    }

    public function test_roomTypeData_structure(): void
    {
        // 通过反射验证load方法中会创建的房型数据结构
        $reflection = new \ReflectionClass(RoomTypeFixtures::class);
        
        // 检查方法内容是否包含预期的房型名称（通过源代码分析）
        $sourceFile = $reflection->getFileName();
        $source = file_get_contents($sourceFile);
        
        // 验证包含预期的房型名称
        $this->assertStringContainsString('豪华大床房', $source);
        $this->assertStringContainsString('行政套房', $source);
        $this->assertStringContainsString('商务大床房', $source);
        $this->assertStringContainsString('标准大床房', $source);
        
        // 验证包含房型属性
        $this->assertStringContainsString('area', $source);
        $this->assertStringContainsString('bedType', $source);
        $this->assertStringContainsString('maxGuests', $source);
        $this->assertStringContainsString('breakfastCount', $source);
        
        // 验证包含停用状态的房型
        $this->assertStringContainsString('RoomTypeStatusEnum::DISABLED', $source);
    }

    public function test_roomTypeEntityCreation_manually(): void
    {
        // 手动验证能创建RoomType实体并设置预期数据
        $hotel = new Hotel();
        $hotel->setName('测试酒店');
        
        $roomType = new RoomType();
        $roomType->setHotel($hotel);
        $roomType->setName('测试房型');
        $roomType->setArea(30.5);
        $roomType->setBedType('大床');
        $roomType->setMaxGuests(2);
        $roomType->setBreakfastCount(1);
        $roomType->setPhotos(['test.jpg']);
        $roomType->setDescription('测试描述');
        
        $this->assertEquals($hotel, $roomType->getHotel());
        $this->assertEquals('测试房型', $roomType->getName());
        $this->assertEquals(30.5, $roomType->getArea());
        $this->assertEquals('大床', $roomType->getBedType());
        $this->assertEquals(2, $roomType->getMaxGuests());
        $this->assertEquals(1, $roomType->getBreakfastCount());
        $this->assertEquals(['test.jpg'], $roomType->getPhotos());
        $this->assertEquals('测试描述', $roomType->getDescription());
    }

    public function test_fixtures_canBeInstantiated(): void
    {
        $fixtures = new RoomTypeFixtures();
        $this->assertInstanceOf(RoomTypeFixtures::class, $fixtures);
        $this->assertInstanceOf(Fixture::class, $fixtures);
        $this->assertInstanceOf(FixtureGroupInterface::class, $fixtures);
        $this->assertInstanceOf(DependentFixtureInterface::class, $fixtures);
    }
} 