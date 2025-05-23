<?php

namespace Tourze\HotelProfileBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Tourze\HotelProfileBundle\DependencyInjection\HotelProfileExtension;
use Tourze\HotelProfileBundle\Service\HotelImportExportService;

class HotelProfileExtensionTest extends TestCase
{
    private HotelProfileExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new HotelProfileExtension();
        $this->container = new ContainerBuilder();
    }

    public function test_extension_extendsSymfonyExtension(): void
    {
        $reflection = new \ReflectionClass(HotelProfileExtension::class);
        $this->assertTrue($reflection->isSubclassOf(Extension::class));
    }

    public function test_load_registersServices(): void
    {
        $this->extension->load([], $this->container);
        
        // 验证服务是否已注册
        $this->assertTrue($this->container->hasDefinition(HotelImportExportService::class));
        
        // 验证服务定义
        $serviceDefinition = $this->container->getDefinition(HotelImportExportService::class);
        $this->assertFalse($serviceDefinition->isPublic());
        $this->assertTrue($serviceDefinition->isAutowired());
        $this->assertTrue($serviceDefinition->isAutoconfigured());
    }

    public function test_load_withEmptyConfig_worksCorrectly(): void
    {
        $this->extension->load([], $this->container);
        
        $this->assertTrue($this->container->hasDefinition(HotelImportExportService::class));
    }

    public function test_load_withMultipleConfigs_mergesCorrectly(): void
    {
        $config1 = [];
        $config2 = [];
        
        $this->extension->load([$config1, $config2], $this->container);
        
        $this->assertTrue($this->container->hasDefinition(HotelImportExportService::class));
    }

    public function test_getAlias_returnsCorrectAlias(): void
    {
        $this->assertEquals('hotel_profile', $this->extension->getAlias());
    }

    public function test_extension_hasCorrectNamespace(): void
    {
        $reflection = new \ReflectionClass(HotelProfileExtension::class);
        $this->assertEquals('Tourze\HotelProfileBundle\DependencyInjection', $reflection->getNamespaceName());
    }

    public function test_load_registersExpectedNumberOfServices(): void
    {
        $initialServiceCount = count($this->container->getDefinitions());
        
        $this->extension->load([], $this->container);
        
        $finalServiceCount = count($this->container->getDefinitions());
        
        // 应该至少注册了一个服务
        $this->assertGreaterThan($initialServiceCount, $finalServiceCount);
    }

    public function test_serviceDefinition_hasCorrectClass(): void
    {
        $this->extension->load([], $this->container);
        
        $serviceDefinition = $this->container->getDefinition(HotelImportExportService::class);
        $this->assertEquals(HotelImportExportService::class, $serviceDefinition->getClass());
    }
} 