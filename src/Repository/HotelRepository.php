<?php

namespace Tourze\HotelProfileBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * 酒店仓库类
 *
 * @extends ServiceEntityRepository<Hotel>
 */
#[AsRepository(entityClass: Hotel::class)]
class HotelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hotel::class);
    }

    /**
     * 根据名称查找酒店
     *
     * @return array<Hotel>
     */
    public function findByName(string $name): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('h.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找特定星级的酒店
     *
     * @return array<Hotel>
     */
    public function findByStarLevel(int $starLevel): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.starLevel = :starLevel')
            ->setParameter('starLevel', $starLevel)
            ->orderBy('h.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找正在运营的酒店
     *
     * @return array<Hotel>
     */
    public function findOperatingHotels(): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.status = :status')
            ->setParameter('status', 'operating')
            ->orderBy('h.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 保存酒店实体
     */
    public function save(Hotel $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 删除酒店实体
     */
    public function remove(Hotel $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
