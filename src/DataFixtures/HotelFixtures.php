<?php

namespace Tourze\HotelProfileBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;

#[When(env: 'test')]
class HotelFixtures extends Fixture
{
    public const LUXURY_HOTEL_REFERENCE = 'luxury-hotel';
    public const BUSINESS_HOTEL_REFERENCE = 'business-hotel';
    public const BUDGET_HOTEL_REFERENCE = 'budget-hotel';
    public const SUSPENDED_HOTEL_REFERENCE = 'suspended-hotel';

    public function load(ObjectManager $manager): void
    {
        $luxuryHotel = new Hotel();
        $luxuryHotel->setName('金辉国际大酒店');
        $luxuryHotel->setAddress('北京市朝阳区建国门外大街88号');
        $luxuryHotel->setStarLevel(5);
        $luxuryHotel->setContactPerson('陈经理');
        $luxuryHotel->setPhone('010-85996666');
        $luxuryHotel->setEmail('chen@jinhui-hotel.com');
        $luxuryHotel->setPhotos([
            'https://img10.360buyimg.com/n1/jfs/t1/129094/15/27735/215398/62621705Ec75b3a2a/932bba946a3f5521.jpg',
            'https://img13.360buyimg.com/n1/jfs/t1/217032/28/31706/47944/647da85aF2eae913f/5584b23380c22119.jpg',
            'https://img12.360buyimg.com/n1/jfs/t1/89794/2/14659/187023/5e6c1c4dEe1b5b2c4/6c8e4e5e5e5e5e5e.jpg',
        ]);
        $luxuryHotel->setFacilities([
            '免费WiFi',
            '免费停车场',
            '健身房',
            'SPA中心',
            '中西餐厅',
        ]);
        $luxuryHotel->setStatus(HotelStatusEnum::OPERATING);

        $manager->persist($luxuryHotel);

        $businessHotel = new Hotel();
        $businessHotel->setName('商务精选酒店');
        $businessHotel->setAddress('上海市浦东新区世纪大道1000号');
        $businessHotel->setStarLevel(4);
        $businessHotel->setContactPerson('王主管');
        $businessHotel->setPhone('021-58881234');
        $businessHotel->setEmail('wang@business-select.com');
        $businessHotel->setPhotos([
            'https://img14.360buyimg.com/n1/jfs/t1/98765/12/23456/123456/5e7c2d3eE1a2b3c4d/7d8e9f0a1b2c3d4e.jpg',
            'https://img15.360buyimg.com/n1/jfs/t1/87654/21/34567/234567/5e8d3e4fE2b3c4d5e/8e9f0a1b2c3d4e5f.jpg',
        ]);
        $businessHotel->setFacilities([
            '高速WiFi',
            '会议室',
            '商务中心',
        ]);
        $businessHotel->setStatus(HotelStatusEnum::OPERATING);

        $manager->persist($businessHotel);

        $budgetHotel = new Hotel();
        $budgetHotel->setName('快捷之家连锁酒店');
        $budgetHotel->setAddress('广州市天河区珠江新城核心区');
        $budgetHotel->setStarLevel(3);
        $budgetHotel->setContactPerson('李店长');
        $budgetHotel->setPhone('020-38888888');
        $budgetHotel->setPhotos(['https://img16.360buyimg.com/n1/jfs/t1/76543/32/45678/345678/5e9e4f5gE3c4d5e6f/9f0a1b2c3d4e5f6g.jpg']);
        $budgetHotel->setFacilities([
            '免费WiFi',
            '免费早餐',
        ]);
        $budgetHotel->setStatus(HotelStatusEnum::OPERATING);

        $manager->persist($budgetHotel);

        $suspendedHotel = new Hotel();
        $suspendedHotel->setName('暂停营业酒店');
        $suspendedHotel->setAddress('深圳市南山区科技园南区');
        $suspendedHotel->setStarLevel(4);
        $suspendedHotel->setContactPerson('刘经理');
        $suspendedHotel->setPhone('0755-26666666');
        $suspendedHotel->setEmail('liu@suspended-hotel.com');
        $suspendedHotel->setStatus(HotelStatusEnum::SUSPENDED);

        $manager->persist($suspendedHotel);

        $manager->flush();

        $this->addReference(self::LUXURY_HOTEL_REFERENCE, $luxuryHotel);
        $this->addReference(self::BUSINESS_HOTEL_REFERENCE, $businessHotel);
        $this->addReference(self::BUDGET_HOTEL_REFERENCE, $budgetHotel);
        $this->addReference(self::SUSPENDED_HOTEL_REFERENCE, $suspendedHotel);
    }
}
