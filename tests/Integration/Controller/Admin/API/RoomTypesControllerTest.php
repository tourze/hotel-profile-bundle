<?php

namespace Tourze\HotelProfileBundle\Tests\Integration\Controller\Admin\API;

use PHPUnit\Framework\TestCase;
use Tourze\HotelProfileBundle\Controller\Admin\API\RoomTypesController;

class RoomTypesControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists(RoomTypesController::class));
    }
}