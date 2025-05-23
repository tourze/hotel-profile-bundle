<?php

namespace Tourze\HotelProfileBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Entity\RoomType;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;

class HotelTest extends TestCase
{
    private Hotel $hotel;

    protected function setUp(): void
    {
        $this->hotel = new Hotel();
    }

    public function test_construct_createsEmptyRoomTypesCollection(): void
    {
        $hotel = new Hotel();
        $this->assertCount(0, $hotel->getRoomTypes());
    }

    public function test_toString_returnsHotelName(): void
    {
        $this->hotel->setName('测试酒店');
        $this->assertEquals('测试酒店', (string)$this->hotel);
    }

    public function test_setName_andGetName_worksCorrectly(): void
    {
        $name = '豪华大酒店';
        $this->hotel->setName($name);
        $this->assertEquals($name, $this->hotel->getName());
    }

    public function test_setAddress_andGetAddress_worksCorrectly(): void
    {
        $address = '北京市朝阳区某某路123号';
        $this->hotel->setAddress($address);
        $this->assertEquals($address, $this->hotel->getAddress());
    }

    public function test_setStarLevel_andGetStarLevel_worksCorrectly(): void
    {
        $starLevel = 5;
        $this->hotel->setStarLevel($starLevel);
        $this->assertEquals($starLevel, $this->hotel->getStarLevel());
    }

    public function test_setContactPerson_andGetContactPerson_worksCorrectly(): void
    {
        $contactPerson = '张经理';
        $this->hotel->setContactPerson($contactPerson);
        $this->assertEquals($contactPerson, $this->hotel->getContactPerson());
    }

    public function test_setPhone_andGetPhone_worksCorrectly(): void
    {
        $phone = '13888888888';
        $this->hotel->setPhone($phone);
        $this->assertEquals($phone, $this->hotel->getPhone());
    }

    public function test_setEmail_andGetEmail_worksCorrectly(): void
    {
        $email = 'manager@hotel.com';
        $this->hotel->setEmail($email);
        $this->assertEquals($email, $this->hotel->getEmail());
    }

    public function test_setEmail_withNull_worksCorrectly(): void
    {
        $this->hotel->setEmail(null);
        $this->assertNull($this->hotel->getEmail());
    }

    public function test_setPhotos_andGetPhotos_worksCorrectly(): void
    {
        $photos = ['photo1.jpg', 'photo2.jpg'];
        $this->hotel->setPhotos($photos);
        $this->assertEquals($photos, $this->hotel->getPhotos());
    }

    public function test_setFacilities_andGetFacilities_worksCorrectly(): void
    {
        $facilities = ['游泳池', '健身房', '餐厅'];
        $this->hotel->setFacilities($facilities);
        $this->assertEquals($facilities, $this->hotel->getFacilities());
    }

    public function test_setStatus_andGetStatus_worksCorrectly(): void
    {
        $status = HotelStatusEnum::SUSPENDED;
        $this->hotel->setStatus($status);
        $this->assertEquals($status, $this->hotel->getStatus());
    }

    public function test_getStatus_defaultValue_isOperating(): void
    {
        $hotel = new Hotel();
        $this->assertEquals(HotelStatusEnum::OPERATING, $hotel->getStatus());
    }

    public function test_addRoomType_addsRoomTypeToCollection(): void
    {
        $roomType = new RoomType();
        $roomType->setName('标准间');

        $this->hotel->addRoomType($roomType);

        $this->assertCount(1, $this->hotel->getRoomTypes());
        $this->assertTrue($this->hotel->getRoomTypes()->contains($roomType));
        $this->assertEquals($this->hotel, $roomType->getHotel());
    }

    public function test_addRoomType_withSameRoomType_doesNotDuplicate(): void
    {
        $roomType = new RoomType();
        $roomType->setName('标准间');

        $this->hotel->addRoomType($roomType);
        $this->hotel->addRoomType($roomType);

        $this->assertCount(1, $this->hotel->getRoomTypes());
    }

    public function test_removeRoomType_removesRoomTypeFromCollection(): void
    {
        $roomType = new RoomType();
        $roomType->setName('标准间');

        $this->hotel->addRoomType($roomType);
        $this->assertCount(1, $this->hotel->getRoomTypes());

        $this->hotel->removeRoomType($roomType);

        $this->assertCount(0, $this->hotel->getRoomTypes());
        $this->assertNull($roomType->getHotel());
    }

    public function test_removeRoomType_withUnrelatedRoomType_doesNothing(): void
    {
        $roomType1 = new RoomType();
        $roomType1->setName('标准间');
        $roomType2 = new RoomType();
        $roomType2->setName('豪华间');

        $this->hotel->addRoomType($roomType1);
        $this->assertCount(1, $this->hotel->getRoomTypes());

        $this->hotel->removeRoomType($roomType2);

        $this->assertCount(1, $this->hotel->getRoomTypes());
        $this->assertTrue($this->hotel->getRoomTypes()->contains($roomType1));
    }

    public function test_createTime_getterAndSetter(): void
    {
        $createTime = new \DateTime('2024-01-01 10:00:00');
        $this->hotel->setCreateTime($createTime);
        $this->assertEquals($createTime, $this->hotel->getCreateTime());
    }

    public function test_updateTime_getterAndSetter(): void
    {
        $updateTime = new \DateTime('2024-01-02 15:30:00');
        $this->hotel->setUpdateTime($updateTime);
        $this->assertEquals($updateTime, $this->hotel->getUpdateTime());
    }

    public function test_createTime_withNull(): void
    {
        $this->hotel->setCreateTime(null);
        $this->assertNull($this->hotel->getCreateTime());
    }

    public function test_updateTime_withNull(): void
    {
        $this->hotel->setUpdateTime(null);
        $this->assertNull($this->hotel->getUpdateTime());
    }

    public function test_getId_initiallyNull(): void
    {
        $this->assertNull($this->hotel->getId());
    }

    public function test_setPhotos_withEmptyArray(): void
    {
        $this->hotel->setPhotos([]);
        $this->assertEquals([], $this->hotel->getPhotos());
    }

    public function test_setFacilities_withEmptyArray(): void
    {
        $this->hotel->setFacilities([]);
        $this->assertEquals([], $this->hotel->getFacilities());
    }

    public function test_defaultValues_afterConstruction(): void
    {
        $hotel = new Hotel();

        $this->assertEquals('', $hotel->getName());
        $this->assertEquals('', $hotel->getAddress());
        $this->assertEquals(3, $hotel->getStarLevel());
        $this->assertEquals('', $hotel->getContactPerson());
        $this->assertEquals('', $hotel->getPhone());
        $this->assertNull($hotel->getEmail());
        $this->assertEquals([], $hotel->getPhotos());
        $this->assertEquals([], $hotel->getFacilities());
        $this->assertEquals(HotelStatusEnum::OPERATING, $hotel->getStatus());
        $this->assertNull($hotel->getCreateTime());
        $this->assertNull($hotel->getUpdateTime());
    }
}
