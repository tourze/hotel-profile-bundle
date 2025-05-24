<?php

namespace Tourze\HotelProfileBundle\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\HotelProfileBundle\DataFixtures\HotelFixtures;
use Tourze\HotelProfileBundle\Entity\Hotel;

class HotelFixturesTest extends TestCase
{
    private HotelFixtures $fixtures;
    private ObjectManager&MockObject $objectManager;

    protected function setUp(): void
    {
        $this->fixtures = new HotelFixtures();
        $this->objectManager = $this->createMock(ObjectManager::class);
    }

    public function test_fixture_extendsFixture(): void
    {
        $reflection = new \ReflectionClass(HotelFixtures::class);
        $this->assertTrue($reflection->isSubclassOf(Fixture::class));
    }

    public function test_fixture_implementsFixtureGroupInterface(): void
    {
        $reflection = new \ReflectionClass(HotelFixtures::class);
        $this->assertTrue($reflection->implementsInterface(FixtureGroupInterface::class));
    }

    public function test_getGroups_returnsCorrectGroups(): void
    {
        $groups = HotelFixtures::getGroups();
        
        $this->assertIsArray($groups);
        $this->assertContains('dev', $groups);
        $this->assertContains('test', $groups);
        $this->assertCount(2, $groups);
    }

    public function test_constants_areDefinedCorrectly(): void
    {
        $reflection = new \ReflectionClass(HotelFixtures::class);
        
        $this->assertTrue($reflection->hasConstant('HOTEL_REFERENCE_PREFIX'));
        $this->assertTrue($reflection->hasConstant('FIVE_STAR_HOTEL_REFERENCE'));
        $this->assertTrue($reflection->hasConstant('FOUR_STAR_HOTEL_REFERENCE'));
        $this->assertTrue($reflection->hasConstant('THREE_STAR_HOTEL_REFERENCE'));
        
        $this->assertEquals('hotel-', HotelFixtures::HOTEL_REFERENCE_PREFIX);
        $this->assertEquals('hotel-five-star', HotelFixtures::FIVE_STAR_HOTEL_REFERENCE);
        $this->assertEquals('hotel-four-star', HotelFixtures::FOUR_STAR_HOTEL_REFERENCE);
        $this->assertEquals('hotel-three-star', HotelFixtures::THREE_STAR_HOTEL_REFERENCE);
    }

    public function test_fixture_hasCorrectNamespace(): void
    {
        $reflection = new \ReflectionClass(HotelFixtures::class);
        $this->assertEquals('Tourze\HotelProfileBundle\DataFixtures', $reflection->getNamespaceName());
    }

    public function test_load_methodSignature_isCorrect(): void
    {
        $reflection = new \ReflectionClass(HotelFixtures::class);
        $method = $reflection->getMethod('load');
        
        $this->assertTrue($method->isPublic());
        $this->assertCount(1, $method->getParameters());
        
        $parameter = $method->getParameters()[0];
        $this->assertEquals('manager', $parameter->getName());
        $this->assertTrue($parameter->hasType());
        $this->assertEquals(ObjectManager::class, $parameter->getType()->getName());
    }

    public function test_classStructure_isCorrect(): void
    {
        $reflection = new \ReflectionClass(HotelFixtures::class);
        
        // 验证类有正确的方法
        $this->assertTrue($reflection->hasMethod('load'));
        $this->assertTrue($reflection->hasMethod('getGroups'));
        
        // 验证类有正确的常量
        $this->assertTrue($reflection->hasConstant('HOTEL_REFERENCE_PREFIX'));
        $this->assertTrue($reflection->hasConstant('FIVE_STAR_HOTEL_REFERENCE'));
        $this->assertTrue($reflection->hasConstant('FOUR_STAR_HOTEL_REFERENCE'));
        $this->assertTrue($reflection->hasConstant('THREE_STAR_HOTEL_REFERENCE'));
        
        // 验证方法可见性
        $loadMethod = $reflection->getMethod('load');
        $this->assertTrue($loadMethod->isPublic());
        
        $getGroupsMethod = $reflection->getMethod('getGroups');
        $this->assertTrue($getGroupsMethod->isPublic());
        $this->assertTrue($getGroupsMethod->isStatic());
    }

    public function test_hotelData_structure(): void
    {
        // 通过反射验证load方法中会创建的酒店数据结构
        $reflection = new \ReflectionClass(HotelFixtures::class);
        $method = $reflection->getMethod('load');
        
        // 验证方法存在且签名正确
        $this->assertTrue($method->isPublic());
        $this->assertCount(1, $method->getParameters());
        
        // 检查方法内容是否包含预期的酒店名称（通过源代码分析）
        $sourceFile = $reflection->getFileName();
        $source = file_get_contents($sourceFile);
        
        // 验证包含预期的酒店名称
        $this->assertStringContainsString('豪华大酒店', $source);
        $this->assertStringContainsString('商务酒店', $source);
        $this->assertStringContainsString('海滨度假酒店', $source);
        $this->assertStringContainsString('城市快捷酒店', $source);
        
        // 验证包含不同星级
        $this->assertStringContainsString("'starLevel' => 5", $source);
        $this->assertStringContainsString("'starLevel' => 4", $source);
        $this->assertStringContainsString("'starLevel' => 3", $source);
        $this->assertStringContainsString("'starLevel' => 2", $source);
    }

    public function test_hotelEntityCreation_manually(): void
    {
        // 手动验证能创建Hotel实体并设置预期数据
        $hotel = new Hotel();
        $hotel->setName('测试酒店');
        $hotel->setAddress('测试地址');
        $hotel->setStarLevel(5);
        $hotel->setContactPerson('测试联系人');
        $hotel->setPhone('13800000000');
        $hotel->setEmail('test@hotel.com');
        $hotel->setFacilities(['餐厅', '健身房']);
        $hotel->setPhotos(['test.jpg']);
        
        $this->assertEquals('测试酒店', $hotel->getName());
        $this->assertEquals('测试地址', $hotel->getAddress());
        $this->assertEquals(5, $hotel->getStarLevel());
        $this->assertEquals('测试联系人', $hotel->getContactPerson());
        $this->assertEquals('13800000000', $hotel->getPhone());
        $this->assertEquals('test@hotel.com', $hotel->getEmail());
        $this->assertEquals(['餐厅', '健身房'], $hotel->getFacilities());
        $this->assertEquals(['test.jpg'], $hotel->getPhotos());
    }

    public function test_fixtures_canBeInstantiated(): void
    {
        $fixtures = new HotelFixtures();
        $this->assertInstanceOf(HotelFixtures::class, $fixtures);
        $this->assertInstanceOf(Fixture::class, $fixtures);
        $this->assertInstanceOf(FixtureGroupInterface::class, $fixtures);
    }
} 