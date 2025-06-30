<?php

namespace Tourze\HotelProfileBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\HotelProfileBundle\DependencyInjection\HotelProfileExtension;

class HotelProfileExtensionTest extends TestCase
{
    public function testExtensionExists(): void
    {
        $this->assertTrue(class_exists(HotelProfileExtension::class));
    }

    public function testLoadServicesConfiguration(): void
    {
        $extension = new HotelProfileExtension();
        $container = new ContainerBuilder();

        $extension->load([], $container);

        $this->assertInstanceOf(ContainerBuilder::class, $container);
    }
}