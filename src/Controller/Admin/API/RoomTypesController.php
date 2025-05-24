<?php

namespace Tourze\HotelProfileBundle\Controller\Admin\API;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Tourze\HotelProfileBundle\Entity\RoomType;

/**
 * 房型API控制器
 */
class RoomTypesController extends AbstractController
{
    /**
     * 获取指定酒店的房型列表
     */
    #[Route('/admin/api/room-types', name: 'admin_api_room_types', methods: ['GET'])]
    public function getRoomTypes(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $hotelId = $request->query->get('hotelId');
        
        if (!$hotelId) {
            return $this->json([]);
        }
        
        $roomTypes = $entityManager->getRepository(RoomType::class)
            ->createQueryBuilder('rt')
            ->select('rt.id', 'rt.name')
            ->where('rt.hotel = :hotelId')
            ->setParameter('hotelId', $hotelId)
            ->orderBy('rt.name', 'ASC')
            ->getQuery()
            ->getArrayResult();
            
        return $this->json($roomTypes);
    }
}
