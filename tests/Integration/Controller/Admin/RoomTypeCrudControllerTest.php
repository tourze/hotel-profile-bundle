<?php

namespace Tourze\HotelProfileBundle\Tests\Integration\Controller\Admin;

use PHPUnit\Framework\TestCase;
use Tourze\HotelProfileBundle\Controller\Admin\RoomTypeCrudController;

class RoomTypeCrudControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists(RoomTypeCrudController::class));
    }
}