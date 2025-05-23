<?php

namespace Tourze\HotelProfileBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\HotelProfileBundle\HotelProfileBundle;

class HotelProfileBundleTest extends TestCase
{
    public function test_bundle_extendsSymfonyBundle(): void
    {
        $reflection = new \ReflectionClass(HotelProfileBundle::class);
        $this->assertTrue($reflection->isSubclassOf(Bundle::class));
    }

    public function test_bundle_canBeInstantiated(): void
    {
        $bundle = new HotelProfileBundle();
        $this->assertInstanceOf(HotelProfileBundle::class, $bundle);
    }

    public function test_bundle_hasCorrectName(): void
    {
        $bundle = new HotelProfileBundle();
        $this->assertEquals('HotelProfileBundle', $bundle->getName());
    }

    public function test_bundle_hasCorrectNamespace(): void
    {
        $bundle = new HotelProfileBundle();
        $this->assertEquals('Tourze\HotelProfileBundle', $bundle->getNamespace());
    }

    public function test_bundle_hasCorrectPath(): void
    {
        $bundle = new HotelProfileBundle();
        $path = $bundle->getPath();
        
        $this->assertStringEndsWith('src', $path);
        $this->assertDirectoryExists($path);
    }
} 