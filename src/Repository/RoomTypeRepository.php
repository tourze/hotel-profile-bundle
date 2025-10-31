<?php

namespace Tourze\HotelProfileBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\HotelProfileBundle\Entity\RoomType;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * 房型仓库类
 *
 * @extends ServiceEntityRepository<RoomType>
 */
#[AsRepository(entityClass: RoomType::class)]
class RoomTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoomType::class);
    }

    /**
     * 根据酒店ID查找房型
     *
     * @return array<RoomType>
     */
    public function findByHotelId(int $hotelId): array
    {
        return $this->createQueryBuilder('rt')
            ->andWhere('rt.hotel = :hotelId')
            ->setParameter('hotelId', $hotelId)
            ->orderBy('rt.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据名称和酒店ID查找房型
     */
    public function findByNameAndHotelId(string $name, int $hotelId): ?RoomType
    {
        return $this->createQueryBuilder('rt')
            ->andWhere('rt.name = :name')
            ->andWhere('rt.hotel = :hotelId')
            ->setParameter('name', $name)
            ->setParameter('hotelId', $hotelId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * 查找可用房型
     *
     * @return array<RoomType>
     */
    public function findActiveRoomTypes(): array
    {
        return $this->createQueryBuilder('rt')
            ->andWhere('rt.status = :status')
            ->setParameter('status', 'active')
            ->orderBy('rt.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 保存房型实体
     */
    public function save(RoomType $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 删除房型实体
     */
    public function remove(RoomType $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
