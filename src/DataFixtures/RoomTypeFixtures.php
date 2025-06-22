<?php

namespace Tourze\HotelProfileBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Entity\RoomType;
use Tourze\HotelProfileBundle\Enum\RoomTypeStatusEnum;

/**
 * 房型数据填充
 */
class RoomTypeFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    // 使用常量定义引用名称
    public const ROOM_TYPE_REFERENCE_PREFIX = 'room-type-';
    
    public function load(ObjectManager $manager): void
    {
        // 为5星级酒店创建房型
        $this->createRoomTypesForHotel(
            $manager, 
            $this->getReference(HotelFixtures::FIVE_STAR_HOTEL_REFERENCE, Hotel::class),
            [
                [
                    'name' => '豪华大床房',
                    'area' => 45.0,
                    'bedType' => '特大床',
                    'maxGuests' => 2,
                    'breakfastCount' => 2,
                    'photos' => [
                        'luxury-king-room-1.jpg',
                        'luxury-king-room-2.jpg',
                    ],
                    'description' => '豪华大床房配备特大床，面积宽敞，提供舒适的居住体验。房间内设有mini吧台、高速WiFi、55英寸智能电视等设施。',
                ],
                [
                    'name' => '行政套房',
                    'area' => 65.0,
                    'bedType' => '特大床',
                    'maxGuests' => 2,
                    'breakfastCount' => 2,
                    'photos' => [
                        'luxury-suite-1.jpg',
                        'luxury-suite-2.jpg',
                    ],
                    'description' => '行政套房设有独立的客厅和卧室，提供专属行政酒廊服务，享有城市美景。',
                ],
                [
                    'name' => '豪华双床房',
                    'area' => 50.0,
                    'bedType' => '两张单人床',
                    'maxGuests' => 3,
                    'breakfastCount' => 2,
                    'photos' => [
                        'luxury-twin-room-1.jpg',
                        'luxury-twin-room-2.jpg',
                    ],
                    'description' => '豪华双床房配备两张舒适单人床，适合商务出行或家庭入住。',
                ],
                [
                    'name' => '总统套房',
                    'area' => 120.0,
                    'bedType' => '特大床',
                    'maxGuests' => 4,
                    'breakfastCount' => 4,
                    'photos' => [
                        'presidential-suite-1.jpg',
                        'presidential-suite-2.jpg',
                    ],
                    'description' => '总统套房是酒店最高级别的住宿选择，设有多个卧室、会客厅和餐厅，提供专属管家服务。',
                ],
            ]
        );
        
        // 为4星级酒店创建房型
        $this->createRoomTypesForHotel(
            $manager, 
            $this->getReference(HotelFixtures::FOUR_STAR_HOTEL_REFERENCE, Hotel::class),
            [
                [
                    'name' => '商务大床房',
                    'area' => 35.0,
                    'bedType' => '大床',
                    'maxGuests' => 2,
                    'breakfastCount' => 2,
                    'photos' => [
                        'business-king-room-1.jpg',
                        'business-king-room-2.jpg',
                    ],
                    'description' => '商务大床房配备舒适大床，提供商务人士所需的各种便利设施。',
                ],
                [
                    'name' => '商务双床房',
                    'area' => 38.0,
                    'bedType' => '两张单人床',
                    'maxGuests' => 3,
                    'breakfastCount' => 2,
                    'photos' => [
                        'business-twin-room-1.jpg',
                        'business-twin-room-2.jpg',
                    ],
                    'description' => '商务双床房适合同事或朋友共同入住，配备两张舒适单人床。',
                ],
                [
                    'name' => '商务套房',
                    'area' => 55.0,
                    'bedType' => '大床',
                    'maxGuests' => 2,
                    'breakfastCount' => 2,
                    'photos' => [
                        'business-suite-1.jpg',
                        'business-suite-2.jpg',
                    ],
                    'description' => '商务套房设有独立的工作区和休息区，提供宽敞舒适的商务环境。',
                ],
            ]
        );
        
        // 为3星级酒店创建房型
        $this->createRoomTypesForHotel(
            $manager, 
            $this->getReference(HotelFixtures::THREE_STAR_HOTEL_REFERENCE, Hotel::class),
            [
                [
                    'name' => '标准大床房',
                    'area' => 25.0,
                    'bedType' => '大床',
                    'maxGuests' => 2,
                    'breakfastCount' => 0,
                    'photos' => [
                        'standard-king-room-1.jpg',
                        'standard-king-room-2.jpg',
                    ],
                    'description' => '标准大床房提供基本的住宿需求，配备舒适大床和必要设施。',
                ],
                [
                    'name' => '标准双床房',
                    'area' => 28.0,
                    'bedType' => '两张单人床',
                    'maxGuests' => 2,
                    'breakfastCount' => 0,
                    'photos' => [
                        'standard-twin-room-1.jpg',
                        'standard-twin-room-2.jpg',
                    ],
                    'description' => '标准双床房配备两张舒适单人床，适合商务出行或好友同行。',
                ],
                [
                    'name' => '经济家庭房',
                    'area' => 32.0,
                    'bedType' => '一张大床和一张单人床',
                    'maxGuests' => 3,
                    'breakfastCount' => 0,
                    'photos' => [
                        'economy-family-room-1.jpg',
                        'economy-family-room-2.jpg',
                    ],
                    'description' => '经济家庭房适合小型家庭入住，配备一张大床和一张单人床。',
                ],
                [
                    'name' => '商务大床房（含早）',
                    'area' => 25.0,
                    'bedType' => '大床',
                    'maxGuests' => 2,
                    'breakfastCount' => 2,
                    'photos' => [
                        'standard-king-room-breakfast-1.jpg',
                        'standard-king-room-breakfast-2.jpg',
                    ],
                    'description' => '商务大床房提供基本的住宿需求和早餐服务，配备舒适大床和必要设施。',
                    'status' => RoomTypeStatusEnum::DISABLED,
                ],
            ]
        );
        
        $manager->flush();
    }
    
    /**
     * 为特定酒店创建多个房型
     *
     * @param ObjectManager $manager 实体管理器
     * @param Hotel $hotel 关联的酒店实体
     * @param array $roomTypesData 房型数据数组
     */
    private function createRoomTypesForHotel(ObjectManager $manager, Hotel $hotel, array $roomTypesData): void
    {
        static $referenceCounter = 1;
        
        foreach ($roomTypesData as $index => $data) {
            $roomType = new RoomType();
            $roomType->setHotel($hotel);
            $roomType->setName($data['name']);
            $roomType->setArea($data['area']);
            $roomType->setBedType($data['bedType']);
            $roomType->setMaxGuests($data['maxGuests']);
            $roomType->setBreakfastCount($data['breakfastCount']);
            $roomType->setPhotos($data['photos']);
            $roomType->setDescription($data['description']);
            
            // 设置状态，默认为ACTIVE
            $roomType->setStatus($data['status'] ?? RoomTypeStatusEnum::ACTIVE);
            
            $manager->persist($roomType);
            
            // 添加引用，使用唯一递增的引用计数器
            $this->addReference(self::ROOM_TYPE_REFERENCE_PREFIX . $referenceCounter, $roomType);
            $referenceCounter++;
        }
    }

    /**
     * 定义依赖的Fixture类
     *
     * @return array<class-string<\Doctrine\Common\DataFixtures\FixtureInterface>>
     */
    public function getDependencies(): array
    {
        return [
            HotelFixtures::class,
        ];
    }

    /**
     * 返回此Fixture所属的组名
     *
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['room', 'hotel-profile', 'default'];
    }
}
