<?php

namespace Tourze\HotelProfileBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;
use Tourze\HotelProfileBundle\Exception\HotelNotFoundException;
use Tourze\HotelProfileBundle\Repository\HotelRepository;

/**
 * 酒店业务服务
 *
 * 简单的示例服务，用于展示集成测试
 */
#[Autoconfigure(public: true)]
readonly class HotelService
{
    public function __construct(
        private HotelRepository $hotelRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * 根据状态查找酒店
     *
     * @return array<Hotel>
     */
    public function findHotelsByStatus(HotelStatusEnum $status): array
    {
        return $this->hotelRepository->findBy(['status' => $status]);
    }

    /**
     * 根据ID查找酒店
     */
    public function findHotelById(int $id): ?Hotel
    {
        return $this->hotelRepository->find($id);
    }

    /**
     * 获取所有酒店
     *
     * @return array<Hotel>
     */
    public function findAllHotels(): array
    {
        return $this->hotelRepository->findAll();
    }

    /**
     * 更新酒店状态
     */
    public function updateHotelStatus(int $hotelId, HotelStatusEnum $newStatus): void
    {
        $hotel = $this->hotelRepository->find($hotelId);

        if (null === $hotel) {
            throw new HotelNotFoundException($hotelId);
        }

        $hotel->setStatus($newStatus);
        $this->entityManager->flush();
    }

    /**
     * 根据名称查找酒店
     */
    public function findHotelByName(string $name): ?Hotel
    {
        return $this->hotelRepository->findOneBy(['name' => $name]);
    }
}
