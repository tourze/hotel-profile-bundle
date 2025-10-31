<?php

namespace Tourze\HotelProfileBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Entity\RoomType;
use Tourze\HotelProfileBundle\Enum\RoomTypeStatusEnum;

#[When(env: 'test')]
class RoomTypeFixtures extends Fixture implements DependentFixtureInterface
{
    public const LUXURY_SUITE_REFERENCE = 'luxury-suite';
    public const BUSINESS_ROOM_REFERENCE = 'business-room';
    public const STANDARD_ROOM_REFERENCE = 'standard-room';
    public const DISABLED_ROOM_REFERENCE = 'disabled-room';

    public function load(ObjectManager $manager): void
    {
        $luxuryHotel = $this->getReference(HotelFixtures::LUXURY_HOTEL_REFERENCE, Hotel::class);

        $businessHotel = $this->getReference(HotelFixtures::BUSINESS_HOTEL_REFERENCE, Hotel::class);

        $budgetHotel = $this->getReference(HotelFixtures::BUDGET_HOTEL_REFERENCE, Hotel::class);

        $luxurySuite = new RoomType();
        $luxurySuite->setHotel($luxuryHotel);
        $luxurySuite->setName('总统套房');
        $luxurySuite->setCode('PRES001');
        $luxurySuite->setArea(150.0);
        $luxurySuite->setBedType('特大床 + 沙发床');
        $luxurySuite->setMaxGuests(4);
        $luxurySuite->setBreakfastCount(2);
        $luxurySuite->setPhotos([
            'https://img17.360buyimg.com/n1/jfs/t1/65432/43/56789/456789/5eaf506hE4d5e6f7g/a0b1c2d3e4f5g6h7.jpg',
            'https://img18.360buyimg.com/n1/jfs/t1/54321/54/67890/567890/5eb0617iE5e6f7g8h/b1c2d3e4f5g6h7i8.jpg',
            'https://img19.360buyimg.com/n1/jfs/t1/43210/65/78901/678901/5ec1728jE6f7g8h9i/c2d3e4f5g6h7i8j9.jpg',
        ]);
        $luxurySuite->setDescription('豪华总统套房，配备独立客厅、卧室，享受顶级服务');
        $luxurySuite->setStatus(RoomTypeStatusEnum::ACTIVE);

        $manager->persist($luxurySuite);

        $businessRoom = new RoomType();
        $businessRoom->setHotel($businessHotel);
        $businessRoom->setName('商务大床房');
        $businessRoom->setCode('BUS001');
        $businessRoom->setArea(35.0);
        $businessRoom->setBedType('大床');
        $businessRoom->setMaxGuests(2);
        $businessRoom->setBreakfastCount(1);
        $businessRoom->setPhotos(['https://img20.360buyimg.com/n1/jfs/t1/32109/76/89012/789012/5ed2839kE7g8h9i0j/d3e4f5g6h7i8j9k0.jpg']);
        $businessRoom->setDescription('专为商务人士设计，配备办公区域');
        $businessRoom->setStatus(RoomTypeStatusEnum::ACTIVE);

        $manager->persist($businessRoom);

        $standardRoom = new RoomType();
        $standardRoom->setHotel($budgetHotel);
        $standardRoom->setName('标准双床房');
        $standardRoom->setCode('STD001');
        $standardRoom->setArea(25.0);
        $standardRoom->setBedType('双床');
        $standardRoom->setMaxGuests(2);
        $standardRoom->setBreakfastCount(0);
        $standardRoom->setPhotos(['https://img21.360buyimg.com/n1/jfs/t1/21098/87/90123/890123/5ee394alE8h9i0j1k/e4f5g6h7i8j9k0l1.jpg']);
        $standardRoom->setDescription('经济实用的标准客房');
        $standardRoom->setStatus(RoomTypeStatusEnum::ACTIVE);

        $manager->persist($standardRoom);

        $disabledRoom = new RoomType();
        $disabledRoom->setHotel($luxuryHotel);
        $disabledRoom->setName('装修中房型');
        $disabledRoom->setCode('RENO001');
        $disabledRoom->setArea(40.0);
        $disabledRoom->setBedType('大床');
        $disabledRoom->setMaxGuests(2);
        $disabledRoom->setBreakfastCount(1);
        $disabledRoom->setDescription('正在装修升级中，暂不开放');
        $disabledRoom->setStatus(RoomTypeStatusEnum::DISABLED);

        $manager->persist($disabledRoom);

        $manager->flush();

        $this->addReference(self::LUXURY_SUITE_REFERENCE, $luxurySuite);
        $this->addReference(self::BUSINESS_ROOM_REFERENCE, $businessRoom);
        $this->addReference(self::STANDARD_ROOM_REFERENCE, $standardRoom);
        $this->addReference(self::DISABLED_ROOM_REFERENCE, $disabledRoom);
    }

    public function getDependencies(): array
    {
        return [
            HotelFixtures::class,
        ];
    }
}
