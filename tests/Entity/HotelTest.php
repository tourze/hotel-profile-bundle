<?php

namespace Tourze\HotelProfileBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Entity\RoomType;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Hotel::class)]
final class HotelTest extends AbstractEntityTestCase
{
    private Hotel $hotel;

    protected function setUp(): void
    {
        $this->hotel = new Hotel();
    }

    public function testConstructorSetsDefaultValues(): void
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

    public function testToStringReturnsHotelName(): void
    {
        // Arrange
        $this->hotel->setName('测试酒店');

        // Act & Assert
        $this->assertEquals('测试酒店', (string) $this->hotel);
    }

    public function testSetNameAndGetNameWorksCorrectly(): void
    {
        // Arrange
        $name = '豪华大酒店';

        // Act
        $this->hotel->setName($name);

        // Assert
        $this->assertEquals($name, $this->hotel->getName());
    }

    public function testSetAddressAndGetAddressWorksCorrectly(): void
    {
        // Arrange
        $address = '北京市朝阳区某某路123号';

        // Act
        $this->hotel->setAddress($address);

        // Assert
        $this->assertEquals($address, $this->hotel->getAddress());
    }

    public function testSetStarLevelAndGetStarLevelWorksCorrectly(): void
    {
        // Arrange
        $starLevel = 5;

        // Act
        $this->hotel->setStarLevel($starLevel);

        // Assert
        $this->assertEquals($starLevel, $this->hotel->getStarLevel());
    }

    public function testSetContactPersonAndGetContactPersonWorksCorrectly(): void
    {
        // Arrange
        $contactPerson = '张经理';

        // Act
        $this->hotel->setContactPerson($contactPerson);

        // Assert
        $this->assertEquals($contactPerson, $this->hotel->getContactPerson());
    }

    public function testSetPhoneAndGetPhoneWorksCorrectly(): void
    {
        // Arrange
        $phone = '13888888888';

        // Act
        $this->hotel->setPhone($phone);

        // Assert
        $this->assertEquals($phone, $this->hotel->getPhone());
    }

    public function testSetEmailAndGetEmailWorksCorrectly(): void
    {
        // Arrange
        $email = 'manager@hotel.com';

        // Act
        $this->hotel->setEmail($email);

        // Assert
        $this->assertEquals($email, $this->hotel->getEmail());
    }

    public function testSetEmailWithNullWorksCorrectly(): void
    {
        // Act
        $this->hotel->setEmail(null);

        // Assert
        $this->assertNull($this->hotel->getEmail());
    }

    public function testSetPhotosAndGetPhotosWorksCorrectly(): void
    {
        // Arrange
        $photos = ['photo1.jpg', 'photo2.jpg'];

        // Act
        $this->hotel->setPhotos($photos);

        // Assert
        $this->assertEquals($photos, $this->hotel->getPhotos());
    }

    public function testSetFacilitiesAndGetFacilitiesWorksCorrectly(): void
    {
        // Arrange
        $facilities = ['游泳池', '健身房', '餐厅'];

        // Act
        $this->hotel->setFacilities($facilities);

        // Assert
        $this->assertEquals($facilities, $this->hotel->getFacilities());
    }

    public function testSetStatusAndGetStatusWorksCorrectly(): void
    {
        // Arrange
        $status = HotelStatusEnum::SUSPENDED;

        // Act
        $this->hotel->setStatus($status);

        // Assert
        $this->assertEquals($status, $this->hotel->getStatus());
    }

    public function testAddRoomTypeAddsRoomTypeToCollection(): void
    {
        // Arrange
        $roomType = new RoomType();
        $roomType->setName('标准间');

        // Act
        $this->hotel->addRoomType($roomType);

        // Assert
        $this->assertCount(1, $this->hotel->getRoomTypes());
        $this->assertTrue($this->hotel->getRoomTypes()->contains($roomType));
        $this->assertEquals($this->hotel, $roomType->getHotel());
    }

    public function testAddRoomTypeWithSameRoomTypeDoesNotDuplicate(): void
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

    public function testRemoveRoomTypeRemovesRoomTypeFromCollection(): void
    {
        // Arrange
        $roomType = new RoomType();
        $roomType->setName('标准间');
        $this->hotel->addRoomType($roomType);

        // Act
        $this->hotel->removeRoomType($roomType);

        // Assert
        $this->assertCount(0, $this->hotel->getRoomTypes());
        $this->assertNull($roomType->getHotel());
    }

    public function testRemoveRoomTypeWithUnrelatedRoomTypeDoesNothing(): void
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

    public function testSetCreateTimeAndGetCreateTimeWorksCorrectly(): void
    {
        // Arrange
        $createTime = new \DateTimeImmutable('2024-01-01 10:00:00');

        // Act
        $this->hotel->setCreateTime($createTime);

        // Assert
        $this->assertEquals($createTime, $this->hotel->getCreateTime());
    }

    public function testSetUpdateTimeAndGetUpdateTimeWorksCorrectly(): void
    {
        // Arrange
        $updateTime = new \DateTimeImmutable('2024-01-02 15:30:00');

        // Act
        $this->hotel->setUpdateTime($updateTime);

        // Assert
        $this->assertEquals($updateTime, $this->hotel->getUpdateTime());
    }

    public function testSetCreateTimeWithNullWorksCorrectly(): void
    {
        // Act
        $this->hotel->setCreateTime(null);

        // Assert
        $this->assertNull($this->hotel->getCreateTime());
    }

    public function testSetUpdateTimeWithNullWorksCorrectly(): void
    {
        // Act
        $this->hotel->setUpdateTime(null);

        // Assert
        $this->assertNull($this->hotel->getUpdateTime());
    }

    protected function createEntity(): object
    {
        return new Hotel();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'name' => ['name', '测试酒店'];
        yield 'address' => ['address', '北京市朝阳区某某路123号'];
        yield 'starLevel' => ['starLevel', 5];
        yield 'contactPerson' => ['contactPerson', '张经理'];
        yield 'phone' => ['phone', '13888888888'];
        yield 'email' => ['email', 'manager@hotel.com'];
        yield 'photos' => ['photos', ['photo1.jpg', 'photo2.jpg']];
        yield 'facilities' => ['facilities', ['游泳池', '健身房']];
        yield 'status' => ['status', HotelStatusEnum::SUSPENDED];
    }

    public function testGetIdInitiallyNull(): void
    {
        // Assert
        $this->assertNull($this->hotel->getId());
    }

    public function testSetPhotosWithEmptyArrayWorksCorrectly(): void
    {
        // Act
        $this->hotel->setPhotos([]);

        // Assert
        $this->assertEquals([], $this->hotel->getPhotos());
    }

    public function testSetFacilitiesWithEmptyArrayWorksCorrectly(): void
    {
        // Act
        $this->hotel->setFacilities([]);

        // Assert
        $this->assertEquals([], $this->hotel->getFacilities());
    }

    public function testBidirectionalAssociationWithRoomType(): void
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
