<?php

namespace Tourze\HotelProfileBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;

/**
 * 酒店数据填充
 * 创建测试用的酒店数据
 */
class HotelFixtures extends Fixture implements FixtureGroupInterface
{
    // 定义引用常量，以便其他Fixture使用
    public const HOTEL_REFERENCE_PREFIX = 'hotel-';
    public const FIVE_STAR_HOTEL_REFERENCE = 'hotel-five-star';
    public const FOUR_STAR_HOTEL_REFERENCE = 'hotel-four-star';
    public const THREE_STAR_HOTEL_REFERENCE = 'hotel-three-star';
    
    public function load(ObjectManager $manager): void
    {
        // 创建10个测试酒店
        $hotels = [
            [
                'name' => '豪华大酒店',
                'address' => '北京市朝阳区建国路88号',
                'contactPerson' => '张经理',
                'phone' => '010-88889999',
                'email' => 'luxury@example.com',
                'starLevel' => 5,
                'status' => HotelStatusEnum::OPERATING,
                'facilities' => ['餐厅', '健身房', '游泳池', '商务中心', 'SPA', '接机服务', '会议室', '高速WiFi'],
                'photos' => ['luxury-hotel-exterior.jpg', 'luxury-hotel-lobby.jpg', 'luxury-hotel-room.jpg'],
            ],
            [
                'name' => '商务酒店',
                'address' => '上海市浦东新区陆家嘴金融中心',
                'contactPerson' => '李总监',
                'phone' => '021-66667777',
                'email' => 'business@example.com',
                'starLevel' => 4,
                'status' => HotelStatusEnum::OPERATING,
                'facilities' => ['餐厅', '健身房', '商务中心', '会议室', '高速WiFi'],
                'photos' => ['business-hotel-exterior.jpg', 'business-hotel-lobby.jpg', 'business-hotel-room.jpg'],
            ],
            [
                'name' => '海滨度假酒店',
                'address' => '三亚市海棠湾度假区',
                'contactPerson' => '王总',
                'phone' => '0898-88776655',
                'email' => 'beach@example.com',
                'starLevel' => 5,
                'status' => HotelStatusEnum::OPERATING,
                'facilities' => ['餐厅', '游泳池', 'SPA', '海滩', '水上运动', '度假村活动', '酒吧'],
                'photos' => ['beach-hotel-exterior.jpg', 'beach-hotel-beach.jpg', 'beach-hotel-room.jpg'],
            ],
            [
                'name' => '城市快捷酒店',
                'address' => '广州市天河区体育中心路',
                'contactPerson' => '黄经理',
                'phone' => '020-33334444',
                'email' => 'express@example.com',
                'starLevel' => 3,
                'status' => HotelStatusEnum::OPERATING,
                'facilities' => ['免费WiFi', '24小时前台', '商务中心'],
                'photos' => ['express-hotel-exterior.jpg', 'express-hotel-room.jpg'],
            ],
            [
                'name' => '古城精品酒店',
                'address' => '西安市碑林区南门内',
                'contactPerson' => '赵经理',
                'phone' => '029-87654321',
                'email' => 'boutique@example.com',
                'starLevel' => 4,
                'status' => HotelStatusEnum::OPERATING,
                'facilities' => ['特色餐厅', 'WiFi', '茶室', '文化体验活动'],
                'photos' => ['boutique-hotel-exterior.jpg', 'boutique-hotel-room.jpg', 'boutique-hotel-lobby.jpg'],
            ],
            [
                'name' => '山水温泉酒店',
                'address' => '杭州市西湖区龙井路',
                'contactPerson' => '林总',
                'phone' => '0571-12345678',
                'email' => 'spa@example.com',
                'starLevel' => 5,
                'status' => HotelStatusEnum::OPERATING,
                'facilities' => ['温泉', 'SPA', '健身房', '精品餐厅', '会议室', '商务中心'],
                'photos' => ['spa-hotel-exterior.jpg', 'spa-hotel-spa.jpg', 'spa-hotel-room.jpg'],
            ],
            [
                'name' => '经济连锁酒店',
                'address' => '成都市武侯区科华北路',
                'contactPerson' => '何经理',
                'phone' => '028-55556666',
                'email' => 'budget@example.com',
                'starLevel' => 2,
                'status' => HotelStatusEnum::OPERATING,
                'facilities' => ['免费WiFi', '自助早餐', '24小时前台'],
                'photos' => ['budget-hotel-exterior.jpg', 'budget-hotel-room.jpg'],
            ],
            [
                'name' => '青年旅舍',
                'address' => '丽江市古城区七一街',
                'contactPerson' => '周经理',
                'phone' => '0888-77889900',
                'email' => 'hostel@example.com',
                'starLevel' => 2,
                'status' => HotelStatusEnum::OPERATING,
                'facilities' => ['公共区域WiFi', '自助厨房', '行李寄存', '旅游信息'],
                'photos' => ['hostel-exterior.jpg', 'hostel-common-room.jpg', 'hostel-dorm.jpg'],
            ],
            [
                'name' => '湖畔花园酒店',
                'address' => '苏州市姑苏区金鸡湖',
                'contactPerson' => '宋总',
                'phone' => '0512-98765432',
                'email' => 'lakeside@example.com',
                'starLevel' => 4,
                'status' => HotelStatusEnum::OPERATING,
                'facilities' => ['湖景餐厅', '花园', '会议室', '商务中心', '高速WiFi'],
                'photos' => ['lakeside-hotel-exterior.jpg', 'lakeside-hotel-garden.jpg', 'lakeside-hotel-room.jpg'],
            ],
            [
                'name' => '智能科技酒店',
                'address' => '深圳市南山区科技园',
                'contactPerson' => '赵经理',
                'phone' => '0755-11223344',
                'email' => 'smart@example.com',
                'starLevel' => 4,
                'status' => HotelStatusEnum::SUSPENDED,
                'facilities' => ['智能客控', '机器人服务', '高速WiFi', '商务中心', '健身房'],
                'photos' => ['smart-hotel-exterior.jpg', 'smart-hotel-tech.jpg', 'smart-hotel-room.jpg'],
            ],
        ];
        
        foreach ($hotels as $index => $hotelData) {
            $hotel = new Hotel();
            $hotel->setName($hotelData['name']);
            $hotel->setAddress($hotelData['address']);
            $hotel->setContactPerson($hotelData['contactPerson']);
            $hotel->setPhone($hotelData['phone']);
            $hotel->setEmail($hotelData['email']);
            $hotel->setStarLevel($hotelData['starLevel']);
            $hotel->setStatus($hotelData['status']);
            $hotel->setFacilities($hotelData['facilities']);
            $hotel->setPhotos($hotelData['photos']);
            
            $manager->persist($hotel);
            
            // 添加引用以供HotelContractFixtures使用
            $this->addReference(self::HOTEL_REFERENCE_PREFIX . ($index + 1), $hotel);
            
            // 添加特定星级酒店的引用
            if ($hotelData['starLevel'] === 5 && !isset($fiveStarHotelAdded)) {
                $this->addReference(self::FIVE_STAR_HOTEL_REFERENCE, $hotel);
                $fiveStarHotelAdded = true;
            } else if ($hotelData['starLevel'] === 4 && !isset($fourStarHotelAdded)) {
                $this->addReference(self::FOUR_STAR_HOTEL_REFERENCE, $hotel);
                $fourStarHotelAdded = true;
            } else if ($hotelData['starLevel'] === 3 && !isset($threeStarHotelAdded)) {
                $this->addReference(self::THREE_STAR_HOTEL_REFERENCE, $hotel);
                $threeStarHotelAdded = true;
            }
        }
        
        $manager->flush();
    }
    
    /**
     * 返回此Fixture所属的组名
     *
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['dev', 'test'];
    }
}
