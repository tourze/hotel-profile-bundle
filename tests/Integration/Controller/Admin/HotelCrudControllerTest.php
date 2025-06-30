<?php

namespace Tourze\HotelProfileBundle\Tests\Integration\Controller\Admin;

use PHPUnit\Framework\TestCase;
use Tourze\HotelProfileBundle\Controller\Admin\HotelCrudController;
use Tourze\HotelProfileBundle\Entity\Hotel;

class HotelCrudControllerTest extends TestCase
{
    public function testGetEntityFqcn(): void
    {
        $this->assertSame(Hotel::class, HotelCrudController::getEntityFqcn());
    }

    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists(HotelCrudController::class));
    }
}