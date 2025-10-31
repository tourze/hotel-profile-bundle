<?php

namespace Tourze\HotelProfileBundle\Controller\Admin\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\HotelProfileBundle\Repository\RoomTypeRepository;

/**
 * 房型API控制器
 */
final class RoomTypesController extends AbstractController
{
    /**
     * 获取指定酒店的房型列表
     */
    #[Route(path: '/admin/api/room-types', name: 'admin_api_room_types', methods: ['GET'])]
    public function __invoke(Request $request, RoomTypeRepository $roomTypeRepository): JsonResponse
    {
        $hotelId = $request->query->get('hotelId');

        if (null === $hotelId || '' === $hotelId || false === $hotelId) {
            return $this->json([]);
        }

        $roomTypes = $roomTypeRepository
            ->createQueryBuilder('rt')
            ->select('rt.id', 'rt.name')
            ->where('rt.hotel = :hotelId')
            ->setParameter('hotelId', $hotelId)
            ->orderBy('rt.name', 'ASC')
            ->getQuery()
            ->getArrayResult()
        ;

        return $this->json($roomTypes);
    }
}
