<?php

namespace Tourze\HotelProfileBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Entity\RoomType;
use Tourze\HotelProfileBundle\Enum\RoomTypeStatusEnum;

class RoomTypeTest extends TestCase
{
    private RoomType $roomType;

    protected function setUp(): void
    {
        $this->roomType = new RoomType();
    }

    public function test_toString_withHotel_returnsHotelAndRoomTypeName(): void
    {
        $hotel = new Hotel();
        $hotel->setName('测试酒店');
        
        $this->roomType->setHotel($hotel);
        $this->roomType->setName('豪华间');
        
        $this->assertEquals('测试酒店 - 豪华间', (string) $this->roomType);
    }

    public function test_toString_withoutHotel_returnsRoomTypeName(): void
    {
        $this->roomType->setName('标准间');
        
        $this->assertEquals('标准间', (string) $this->roomType);
    }

    public function test_setHotel_andGetHotel_worksCorrectly(): void
    {
        $hotel = new Hotel();
        $hotel->setName('测试酒店');
        
        $this->roomType->setHotel($hotel);
        
        $this->assertEquals($hotel, $this->roomType->getHotel());
    }

    public function test_setHotel_withNull_worksCorrectly(): void
    {
        $this->roomType->setHotel(null);
        $this->assertNull($this->roomType->getHotel());
    }

    public function test_setName_andGetName_worksCorrectly(): void
    {
        $name = '总统套房';
        $this->roomType->setName($name);
        $this->assertEquals($name, $this->roomType->getName());
    }

    public function test_setCode_andGetCode_worksCorrectly(): void
    {
        $code = 'DELUXE';
        $this->roomType->setCode($code);
        $this->assertEquals($code, $this->roomType->getCode());
    }

    public function test_setCode_withNull_worksCorrectly(): void
    {
        $this->roomType->setCode(null);
        $this->assertNull($this->roomType->getCode());
    }

    public function test_setArea_andGetArea_worksCorrectly(): void
    {
        $area = 45.5;
        $this->roomType->setArea($area);
        $this->assertEquals($area, $this->roomType->getArea());
    }

    public function test_setBedType_andGetBedType_worksCorrectly(): void
    {
        $bedType = '大床';
        $this->roomType->setBedType($bedType);
        $this->assertEquals($bedType, $this->roomType->getBedType());
    }

    public function test_setMaxGuests_andGetMaxGuests_worksCorrectly(): void
    {
        $maxGuests = 4;
        $this->roomType->setMaxGuests($maxGuests);
        $this->assertEquals($maxGuests, $this->roomType->getMaxGuests());
    }

    public function test_setBreakfastCount_andGetBreakfastCount_worksCorrectly(): void
    {
        $breakfastCount = 2;
        $this->roomType->setBreakfastCount($breakfastCount);
        $this->assertEquals($breakfastCount, $this->roomType->getBreakfastCount());
    }

    public function test_setPhotos_andGetPhotos_worksCorrectly(): void
    {
        $photos = ['room1.jpg', 'room2.jpg', 'room3.jpg'];
        $this->roomType->setPhotos($photos);
        $this->assertEquals($photos, $this->roomType->getPhotos());
    }

    public function test_setDescription_andGetDescription_worksCorrectly(): void
    {
        $description = '宽敞明亮的豪华客房，配备现代化设施';
        $this->roomType->setDescription($description);
        $this->assertEquals($description, $this->roomType->getDescription());
    }

    public function test_setDescription_withNull_worksCorrectly(): void
    {
        $this->roomType->setDescription(null);
        $this->assertNull($this->roomType->getDescription());
    }

    public function test_setStatus_andGetStatus_worksCorrectly(): void
    {
        $status = RoomTypeStatusEnum::DISABLED;
        $this->roomType->setStatus($status);
        $this->assertEquals($status, $this->roomType->getStatus());
    }

    public function test_getStatus_defaultValue_isActive(): void
    {
        $roomType = new RoomType();
        $this->assertEquals(RoomTypeStatusEnum::ACTIVE, $roomType->getStatus());
    }

    public function test_createTime_getterAndSetter(): void
    {
        $createTime = new \DateTime('2024-01-01 10:00:00');
        $this->roomType->setCreateTime($createTime);
        $this->assertEquals($createTime, $this->roomType->getCreateTime());
    }

    public function test_updateTime_getterAndSetter(): void
    {
        $updateTime = new \DateTime('2024-01-02 15:30:00');
        $this->roomType->setUpdateTime($updateTime);
        $this->assertEquals($updateTime, $this->roomType->getUpdateTime());
    }

    public function test_createTime_withNull(): void
    {
        $this->roomType->setCreateTime(null);
        $this->assertNull($this->roomType->getCreateTime());
    }

    public function test_updateTime_withNull(): void
    {
        $this->roomType->setUpdateTime(null);
        $this->assertNull($this->roomType->getUpdateTime());
    }

    public function test_getId_initiallyNull(): void
    {
        $this->assertNull($this->roomType->getId());
    }

    public function test_setPhotos_withEmptyArray(): void
    {
        $this->roomType->setPhotos([]);
        $this->assertEquals([], $this->roomType->getPhotos());
    }

    public function test_defaultValues_afterConstruction(): void
    {
        $roomType = new RoomType();
        
        $this->assertNull($roomType->getHotel());
        $this->assertEquals('', $roomType->getName());
        $this->assertNull($roomType->getCode());
        $this->assertEquals(0.0, $roomType->getArea());
        $this->assertEquals('', $roomType->getBedType());
        $this->assertEquals(2, $roomType->getMaxGuests());
        $this->assertEquals(0, $roomType->getBreakfastCount());
        $this->assertEquals([], $roomType->getPhotos());
        $this->assertNull($roomType->getDescription());
        $this->assertEquals(RoomTypeStatusEnum::ACTIVE, $roomType->getStatus());
        $this->assertNull($roomType->getCreateTime());
        $this->assertNull($roomType->getUpdateTime());
    }

    public function test_bidirectionalAssociation_withHotel(): void
    {
        $hotel = new Hotel();
        $hotel->setName('测试酒店');
        
        // 通过Hotel添加RoomType
        $hotel->addRoomType($this->roomType);
        
        $this->assertEquals($hotel, $this->roomType->getHotel());
        $this->assertTrue($hotel->getRoomTypes()->contains($this->roomType));
    }

    public function test_setArea_withFloat_worksCorrectly(): void
    {
        $area = 35.8;
        $this->roomType->setArea($area);
        $this->assertEquals($area, $this->roomType->getArea());
    }

    public function test_setArea_withZero_worksCorrectly(): void
    {
        $this->roomType->setArea(0.0);
        $this->assertEquals(0.0, $this->roomType->getArea());
    }

    public function test_setMaxGuests_withOne_worksCorrectly(): void
    {
        $this->roomType->setMaxGuests(1);
        $this->assertEquals(1, $this->roomType->getMaxGuests());
    }

    public function test_setBreakfastCount_withZero_worksCorrectly(): void
    {
        $this->roomType->setBreakfastCount(0);
        $this->assertEquals(0, $this->roomType->getBreakfastCount());
    }
} 