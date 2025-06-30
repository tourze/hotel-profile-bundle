<?php

namespace Tourze\HotelProfileBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\HotelProfileBundle\HotelProfileBundle;

class HotelProfileBundleTest extends TestCase
{
    public function testBundleExists(): void
    {
        $this->assertTrue(class_exists(HotelProfileBundle::class));
    }

    public function testBundleInstance(): void
    {
        $bundle = new HotelProfileBundle();
        $this->assertInstanceOf(HotelProfileBundle::class, $bundle);
    }

    public function testBundleBuild(): void
    {
        $bundle = new HotelProfileBundle();
        $container = new ContainerBuilder();
        
        $bundle->build($container);
        
        $this->assertInstanceOf(ContainerBuilder::class, $container);
    }
}