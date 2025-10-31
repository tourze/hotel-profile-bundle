<?php

namespace Tourze\HotelProfileBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use Tourze\HotelProfileBundle\Service\AttributeControllerLoader;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;

/**
 * @internal
 */
#[CoversClass(AttributeControllerLoader::class)]
#[RunTestsInSeparateProcesses]
final class AttributeControllerLoaderTest extends AbstractIntegrationTestCase
{
    private AttributeControllerLoader $loader;

    protected function onSetUp(): void
    {
        $this->loader = self::getService(AttributeControllerLoader::class);
    }

    public function testExtendsLoader(): void
    {
        $this->assertInstanceOf(Loader::class, $this->loader);
    }

    public function testImplementsRoutingAutoLoaderInterface(): void
    {
        $this->assertInstanceOf(RoutingAutoLoaderInterface::class, $this->loader);
    }

    public function testLoadReturnsRouteCollection(): void
    {
        $result = $this->loader->load('dummy');

        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function testLoadWithResourceAndType(): void
    {
        $result = $this->loader->load('resource', 'type');

        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function testSupportsReturnsFalse(): void
    {
        $this->assertFalse($this->loader->supports('any_resource'));
        $this->assertFalse($this->loader->supports('any_resource', 'any_type'));
    }

    public function testAutoloadReturnsRouteCollection(): void
    {
        $result = $this->loader->autoload();

        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function testConstructorSetsUpCorrectly(): void
    {
        $loader = self::getService(AttributeControllerLoader::class);

        $this->assertInstanceOf(AttributeControllerLoader::class, $loader);
    }

    public function testHasControllerLoaderProperty(): void
    {
        $reflection = new \ReflectionClass($this->loader);

        $this->assertTrue($reflection->hasProperty('controllerLoader'));
    }

    public function testControllerLoaderPropertyIsPrivate(): void
    {
        $reflection = new \ReflectionClass($this->loader);
        $property = $reflection->getProperty('controllerLoader');

        $this->assertTrue($property->isPrivate());
    }

    public function testLoadCallsAutoload(): void
    {
        // 由于 load 方法调用 autoload，测试它们返回相同类型的对象
        $loadResult = $this->loader->load('dummy');
        $autoloadResult = $this->loader->autoload();

        $this->assertInstanceOf(
            get_class($loadResult),
            $autoloadResult
        );
    }
}
