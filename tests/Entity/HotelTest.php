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

    public function test_constructor_setsDefaultValues(): void
    {
        // Arrange & Act
        $hotel = new Hotel();

        // Assert
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
        $this->assertCount(0, $hotel->getRoomTypes());
    }

    public function test_toString_returnsHotelName(): void
    {
        // Arrange
        $this->hotel->setName('测试酒店');

        // Act & Assert
        $this->assertEquals('测试酒店', (string)$this->hotel);
    }

    public function test_setName_andGetName_worksCorrectly(): void
    {
        // Arrange
        $name = '豪华大酒店';

        // Act
        $result = $this->hotel->setName($name);

        // Assert
        $this->assertSame($this->hotel, $result);
        $this->assertEquals($name, $this->hotel->getName());
    }

    public function test_setAddress_andGetAddress_worksCorrectly(): void
    {
        // Arrange
        $address = '北京市朝阳区某某路123号';

        // Act
        $result = $this->hotel->setAddress($address);

        // Assert
        $this->assertSame($this->hotel, $result);
        $this->assertEquals($address, $this->hotel->getAddress());
    }

    public function test_setStarLevel_andGetStarLevel_worksCorrectly(): void
    {
        // Arrange
        $starLevel = 5;

        // Act
        $result = $this->hotel->setStarLevel($starLevel);

        // Assert
        $this->assertSame($this->hotel, $result);
        $this->assertEquals($starLevel, $this->hotel->getStarLevel());
    }

    public function test_setContactPerson_andGetContactPerson_worksCorrectly(): void
    {
        // Arrange
        $contactPerson = '张经理';

        // Act
        $result = $this->hotel->setContactPerson($contactPerson);

        // Assert
        $this->assertSame($this->hotel, $result);
        $this->assertEquals($contactPerson, $this->hotel->getContactPerson());
    }

    public function test_setPhone_andGetPhone_worksCorrectly(): void
    {
        // Arrange
        $phone = '13888888888';

        // Act
        $result = $this->hotel->setPhone($phone);

        // Assert
        $this->assertSame($this->hotel, $result);
        $this->assertEquals($phone, $this->hotel->getPhone());
    }

    public function test_setEmail_andGetEmail_worksCorrectly(): void
    {
        // Arrange
        $email = 'manager@hotel.com';

        // Act
        $result = $this->hotel->setEmail($email);

        // Assert
        $this->assertSame($this->hotel, $result);
        $this->assertEquals($email, $this->hotel->getEmail());
    }

    public function test_setEmail_withNull_worksCorrectly(): void
    {
        // Act
        $result = $this->hotel->setEmail(null);

        // Assert
        $this->assertSame($this->hotel, $result);
        $this->assertNull($this->hotel->getEmail());
    }

    public function test_setPhotos_andGetPhotos_worksCorrectly(): void
    {
        // Arrange
        $photos = ['photo1.jpg', 'photo2.jpg'];

        // Act
        $result = $this->hotel->setPhotos($photos);

        // Assert
        $this->assertSame($this->hotel, $result);
        $this->assertEquals($photos, $this->hotel->getPhotos());
    }

    public function test_setFacilities_andGetFacilities_worksCorrectly(): void
    {
        // Arrange
        $facilities = ['游泳池', '健身房', '餐厅'];

        // Act
        $result = $this->hotel->setFacilities($facilities);

        // Assert
        $this->assertSame($this->hotel, $result);
        $this->assertEquals($facilities, $this->hotel->getFacilities());
    }

    public function test_setStatus_andGetStatus_worksCorrectly(): void
    {
        // Arrange
        $status = HotelStatusEnum::SUSPENDED;

        // Act
        $result = $this->hotel->setStatus($status);

        // Assert
        $this->assertSame($this->hotel, $result);
        $this->assertEquals($status, $this->hotel->getStatus());
    }

    public function test_addRoomType_addsRoomTypeToCollection(): void
    {
        // Arrange
        $roomType = new RoomType();
        $roomType->setName('标准间');

        // Act
        $result = $this->hotel->addRoomType($roomType);

        // Assert
        $this->assertSame($this->hotel, $result);
        $this->assertCount(1, $this->hotel->getRoomTypes());
        $this->assertTrue($this->hotel->getRoomTypes()->contains($roomType));
        $this->assertEquals($this->hotel, $roomType->getHotel());
    }

    public function test_addRoomType_withSameRoomType_doesNotDuplicate(): void
    {
        // Arrange
        $roomType = new RoomType();
        $roomType->setName('标准间');

        // Act
        $this->hotel->addRoomType($roomType);
        $this->hotel->addRoomType($roomType);

        // Assert
        $this->assertCount(1, $this->hotel->getRoomTypes());
    }

    public function test_removeRoomType_removesRoomTypeFromCollection(): void
    {
        // Arrange
        $roomType = new RoomType();
        $roomType->setName('标准间');
        $this->hotel->addRoomType($roomType);

        // Act
        $result = $this->hotel->removeRoomType($roomType);

        // Assert
        $this->assertSame($this->hotel, $result);
        $this->assertCount(0, $this->hotel->getRoomTypes());
        $this->assertNull($roomType->getHotel());
    }

    public function test_removeRoomType_withUnrelatedRoomType_doesNothing(): void
    {
        // Arrange
        $roomType1 = new RoomType();
        $roomType1->setName('标准间');
        $roomType2 = new RoomType();
        $roomType2->setName('豪华间');

        $this->hotel->addRoomType($roomType1);

        // Act
        $this->hotel->removeRoomType($roomType2);

        // Assert
        $this->assertCount(1, $this->hotel->getRoomTypes());
        $this->assertTrue($this->hotel->getRoomTypes()->contains($roomType1));
    }

    public function test_setCreateTime_andGetCreateTime_worksCorrectly(): void
    {
        // Arrange
        $createTime = new \DateTimeImmutable('2024-01-01 10:00:00');

        // Act
        $this->hotel->setCreateTime($createTime);

        // Assert
        $this->assertEquals($createTime, $this->hotel->getCreateTime());
    }

    public function test_setUpdateTime_andGetUpdateTime_worksCorrectly(): void
    {
        // Arrange
        $updateTime = new \DateTimeImmutable('2024-01-02 15:30:00');

        // Act
        $this->hotel->setUpdateTime($updateTime);

        // Assert
        $this->assertEquals($updateTime, $this->hotel->getUpdateTime());
    }

    public function test_setCreateTime_withNull_worksCorrectly(): void
    {
        // Act
        $this->hotel->setCreateTime(null);

        // Assert
        $this->assertNull($this->hotel->getCreateTime());
    }

    public function test_setUpdateTime_withNull_worksCorrectly(): void
    {
        // Act
        $this->hotel->setUpdateTime(null);

        // Assert
        $this->assertNull($this->hotel->getUpdateTime());
    }

    public function test_getId_initiallyNull(): void
    {
        // Assert
        $this->assertNull($this->hotel->getId());
    }

    public function test_setPhotos_withEmptyArray_worksCorrectly(): void
    {
        // Act
        $this->hotel->setPhotos([]);

        // Assert
        $this->assertEquals([], $this->hotel->getPhotos());
    }

    public function test_setFacilities_withEmptyArray_worksCorrectly(): void
    {
        // Act
        $this->hotel->setFacilities([]);

        // Assert
        $this->assertEquals([], $this->hotel->getFacilities());
    }

    public function test_bidirectionalAssociation_withRoomType(): void
    {
        // Arrange
        $roomType = new RoomType();
        $roomType->setName('测试房型');

        // Act
        $this->hotel->addRoomType($roomType);

        // Assert
        $this->assertEquals($this->hotel, $roomType->getHotel());
        $this->assertTrue($this->hotel->getRoomTypes()->contains($roomType));
    }
}
