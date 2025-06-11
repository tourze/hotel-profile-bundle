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

    public function testInitialState(): void
    {
        $this->assertNull($this->roomType->getId());
        $this->assertNull($this->roomType->getHotel());
        $this->assertSame('', $this->roomType->getName());
        $this->assertNull($this->roomType->getCode());
        $this->assertSame(0.0, $this->roomType->getArea());
        $this->assertSame('', $this->roomType->getBedType());
        $this->assertSame(2, $this->roomType->getMaxGuests());
        $this->assertSame(0, $this->roomType->getBreakfastCount());
        $this->assertSame([], $this->roomType->getPhotos());
        $this->assertNull($this->roomType->getDescription());
        $this->assertNull($this->roomType->getCreateTime());
        $this->assertNull($this->roomType->getUpdateTime());
        $this->assertInstanceOf(RoomTypeStatusEnum::class, $this->roomType->getStatus());
        $this->assertSame(RoomTypeStatusEnum::ACTIVE, $this->roomType->getStatus());
    }

    public function testSetAndGetHotel(): void
    {
        $hotel = new Hotel();
        $hotel->setName('Test Hotel')->setAddress('Test Address')->setStarLevel(5);

        $result = $this->roomType->setHotel($hotel);

        $this->assertSame($this->roomType, $result);
        $this->assertSame($hotel, $this->roomType->getHotel());
    }

    public function testSetAndGetHotelWithNull(): void
    {
        $this->roomType->setHotel(null);

        $this->assertNull($this->roomType->getHotel());
    }

    public function testSetAndGetName(): void
    {
        $name = 'Standard Room';

        $result = $this->roomType->setName($name);

        $this->assertSame($this->roomType, $result);
        $this->assertSame($name, $this->roomType->getName());
    }

    public function testSetAndGetCode(): void
    {
        $code = 'STD001';

        $result = $this->roomType->setCode($code);

        $this->assertSame($this->roomType, $result);
        $this->assertSame($code, $this->roomType->getCode());
    }

    public function testSetAndGetCodeWithNull(): void
    {
        $this->roomType->setCode(null);

        $this->assertNull($this->roomType->getCode());
    }

    public function testSetAndGetArea(): void
    {
        $area = 25.5;

        $result = $this->roomType->setArea($area);

        $this->assertSame($this->roomType, $result);
        $this->assertSame($area, $this->roomType->getArea());
    }

    public function testSetAndGetBedType(): void
    {
        $bedType = 'King Size Bed';

        $result = $this->roomType->setBedType($bedType);

        $this->assertSame($this->roomType, $result);
        $this->assertSame($bedType, $this->roomType->getBedType());
    }

    public function testSetAndGetMaxGuests(): void
    {
        $maxGuests = 4;

        $result = $this->roomType->setMaxGuests($maxGuests);

        $this->assertSame($this->roomType, $result);
        $this->assertSame($maxGuests, $this->roomType->getMaxGuests());
    }

    public function testSetAndGetBreakfastCount(): void
    {
        $breakfastCount = 2;

        $result = $this->roomType->setBreakfastCount($breakfastCount);

        $this->assertSame($this->roomType, $result);
        $this->assertSame($breakfastCount, $this->roomType->getBreakfastCount());
    }

    public function testSetAndGetPhotos(): void
    {
        $photos = ['photo1.jpg', 'photo2.jpg'];

        $result = $this->roomType->setPhotos($photos);

        $this->assertSame($this->roomType, $result);
        $this->assertSame($photos, $this->roomType->getPhotos());
    }

    public function testSetAndGetPhotosEmpty(): void
    {
        $this->roomType->setPhotos([]);

        $this->assertSame([], $this->roomType->getPhotos());
    }

    public function testSetAndGetDescription(): void
    {
        $description = 'A comfortable standard room with city view';

        $result = $this->roomType->setDescription($description);

        $this->assertSame($this->roomType, $result);
        $this->assertSame($description, $this->roomType->getDescription());
    }

    public function testSetAndGetDescriptionWithNull(): void
    {
        $this->roomType->setDescription(null);

        $this->assertNull($this->roomType->getDescription());
    }

    public function testSetAndGetStatus(): void
    {
        $status = RoomTypeStatusEnum::DISABLED;

        $result = $this->roomType->setStatus($status);

        $this->assertSame($this->roomType, $result);
        $this->assertSame($status, $this->roomType->getStatus());
    }

    public function testSetAndGetCreateTime(): void
    {
        $createTime = new \DateTime('2023-01-01 10:00:00');

        $this->roomType->setCreateTime($createTime);

        $this->assertSame($createTime, $this->roomType->getCreateTime());
    }

    public function testSetAndGetCreateTimeWithNull(): void
    {
        $this->roomType->setCreateTime(null);

        $this->assertNull($this->roomType->getCreateTime());
    }

    public function testSetAndGetUpdateTime(): void
    {
        $updateTime = new \DateTime('2023-01-02 10:00:00');

        $this->roomType->setUpdateTime($updateTime);

        $this->assertSame($updateTime, $this->roomType->getUpdateTime());
    }

    public function testSetAndGetUpdateTimeWithNull(): void
    {
        $this->roomType->setUpdateTime(null);

        $this->assertNull($this->roomType->getUpdateTime());
    }

    public function testToStringWithHotel(): void
    {
        $hotel = new Hotel();
        $hotel->setName('Grand Hotel')->setAddress('Main Street 123')->setStarLevel(5);
        $this->roomType->setHotel($hotel);
        $this->roomType->setName('Deluxe Suite');

        $result = (string) $this->roomType;

        $this->assertSame('Grand Hotel - Deluxe Suite', $result);
    }

    public function testToStringWithoutHotel(): void
    {
        $this->roomType->setName('Standard Room');

        $result = (string) $this->roomType;

        $this->assertSame('Standard Room', $result);
    }

    public function testToStringWithEmptyName(): void
    {
        $result = (string) $this->roomType;

        $this->assertSame('', $result);
    }

    public function testCompleteRoomTypeCreation(): void
    {
        $hotel = new Hotel();
        $hotel->setName('Test Hotel')->setAddress('Test Address')->setStarLevel(4);
        $photos = ['room1.jpg', 'room2.jpg'];
        $createTime = new \DateTime('2023-01-01 10:00:00');
        $updateTime = new \DateTime('2023-01-02 10:00:00');

        $this->roomType
            ->setHotel($hotel)
            ->setName('Executive Suite')
            ->setCode('EXE001')
            ->setArea(45.0)
            ->setBedType('King Size Bed')
            ->setMaxGuests(3)
            ->setBreakfastCount(2)
            ->setPhotos($photos)
            ->setDescription('Luxurious executive suite with panoramic view')
            ->setStatus(RoomTypeStatusEnum::ACTIVE);

        $this->roomType->setCreateTime($createTime);
        $this->roomType->setUpdateTime($updateTime);

        $this->assertSame($hotel, $this->roomType->getHotel());
        $this->assertSame('Executive Suite', $this->roomType->getName());
        $this->assertSame('EXE001', $this->roomType->getCode());
        $this->assertSame(45.0, $this->roomType->getArea());
        $this->assertSame('King Size Bed', $this->roomType->getBedType());
        $this->assertSame(3, $this->roomType->getMaxGuests());
        $this->assertSame(2, $this->roomType->getBreakfastCount());
        $this->assertSame($photos, $this->roomType->getPhotos());
        $this->assertSame('Luxurious executive suite with panoramic view', $this->roomType->getDescription());
        $this->assertSame(RoomTypeStatusEnum::ACTIVE, $this->roomType->getStatus());
        $this->assertSame($createTime, $this->roomType->getCreateTime());
        $this->assertSame($updateTime, $this->roomType->getUpdateTime());
        $this->assertSame('Test Hotel - Executive Suite', (string) $this->roomType);
    }

    public function testMethodChaining(): void
    {
        $hotel = new Hotel();
        $hotel->setName('Chain Hotel')->setAddress('Chain Street')->setStarLevel(3);

        $result = $this->roomType
            ->setHotel($hotel)
            ->setName('Standard Room')
            ->setCode('STD001')
            ->setArea(20.0)
            ->setBedType('Double Bed')
            ->setMaxGuests(2)
            ->setBreakfastCount(1)
            ->setPhotos(['photo.jpg'])
            ->setDescription('Standard room')
            ->setStatus(RoomTypeStatusEnum::ACTIVE);

        $this->assertSame($this->roomType, $result);
    }
}
