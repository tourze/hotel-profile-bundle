<?php

namespace Tourze\HotelProfileBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\HotelProfileBundle\Entity\RoomType;
use Tourze\HotelProfileBundle\Enum\RoomTypeStatusEnum;
use Tourze\HotelProfileBundle\Repository\RoomTypeRepository;

/**
 * 房型业务服务
 *
 * 提供房型相关的业务逻辑，供其他模块调用
 */
#[Autoconfigure(public: true)]
readonly class RoomTypeService
{
    public function __construct(
        private RoomTypeRepository $roomTypeRepository,
    ) {
    }

    /**
     * 获取所有房型（含酒店信息）
     *
     * @return array<RoomType>
     */
    public function getAllRoomTypesWithHotel(): array
    {
        return $this->roomTypeRepository
            ->createQueryBuilder('rt')
            ->leftJoin('rt.hotel', 'h')
            ->addSelect('h')
            ->orderBy('h.name', 'ASC')
            ->addOrderBy('rt.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取所有房型
     *
     * @return array<RoomType>
     */
    public function findAllRoomTypes(): array
    {
        return $this->roomTypeRepository->findAll();
    }

    /**
     * 根据状态查找房型
     *
     * @return array<RoomType>
     */
    public function findRoomTypesByStatus(RoomTypeStatusEnum $status): array
    {
        return $this->roomTypeRepository->findBy(['status' => $status]);
    }

    /**
     * 根据酒店查找房型
     *
     * @return array<RoomType>
     */
    public function findRoomTypesByHotel(int $hotelId): array
    {
        return $this->roomTypeRepository->findBy(['hotel' => $hotelId]);
    }

    /**
     * 根据ID查找房型
     */
    public function findRoomTypeById(int $id): ?RoomType
    {
        return $this->roomTypeRepository->find($id);
    }

    /**
     * 根据ID数组查找房型
     *
     * @param array<int> $ids
     *
     * @return array<RoomType>
     */
    public function findRoomTypesByIds(array $ids): array
    {
        if ([] === $ids) {
            return [];
        }

        return $this->roomTypeRepository->findBy(['id' => $ids]);
    }

    /**
     * 根据酒店和状态查找房型
     *
     * @return array<RoomType>
     */
    public function findRoomTypesByHotelAndStatus(int $hotelId, RoomTypeStatusEnum $status): array
    {
        return $this->roomTypeRepository->findBy([
            'hotel' => $hotelId,
            'status' => $status,
        ]);
    }

    /**
     * 根据酒店和名称查找房型
     */
    public function findRoomTypeByHotelAndName(int $hotelId, string $name): ?RoomType
    {
        return $this->roomTypeRepository->findOneBy([
            'hotel' => $hotelId,
            'name' => $name,
        ]);
    }
}
