<?php

namespace Tourze\HotelProfileBundle\Exception;

/**
 * 酒店未找到异常
 */
class HotelNotFoundException extends \RuntimeException
{
    public function __construct(int $hotelId)
    {
        parent::__construct(sprintf('Hotel with ID %d not found', $hotelId), $hotelId);
    }
}
