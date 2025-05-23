<?php

namespace Tourze\HotelProfileBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\HotelProfileBundle\Entity\Hotel;

/**
 * 酒店仓库类
 *
 * @extends ServiceEntityRepository<Hotel>
 *
 * @method Hotel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Hotel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Hotel[]    findAll()
 * @method Hotel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HotelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hotel::class);
    }

    /**
     * 保存酒店实体
     */
    public function save(Hotel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 删除酒店实体
     */
    public function remove(Hotel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 根据名称查找酒店
     */
    public function findByName(string $name): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('h.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找特定星级的酒店
     */
    public function findByStarLevel(int $starLevel): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.starLevel = :starLevel')
            ->setParameter('starLevel', $starLevel)
            ->orderBy('h.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找正在运营的酒店
     */
    public function findOperatingHotels(): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.status = :status')
            ->setParameter('status', 'operating')
            ->orderBy('h.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
