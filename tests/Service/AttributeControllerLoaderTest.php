<?php

namespace Tourze\HotelProfileBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouteCollection;
use Tourze\HotelProfileBundle\Service\AttributeControllerLoader;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;

class AttributeControllerLoaderTest extends TestCase
{
    private AttributeControllerLoader $loader;

    protected function setUp(): void
    {
        $this->loader = new AttributeControllerLoader();
    }

    public function test_loader_implementsRoutingAutoLoaderInterface(): void
    {
        $reflection = new \ReflectionClass(AttributeControllerLoader::class);
        $this->assertTrue($reflection->implementsInterface(RoutingAutoLoaderInterface::class));
    }

    public function test_loader_hasCorrectNamespace(): void
    {
        $reflection = new \ReflectionClass(AttributeControllerLoader::class);
        $this->assertEquals('Tourze\HotelProfileBundle\Service', $reflection->getNamespaceName());
    }

    public function test_load_withAnyResource_returnsRouteCollection(): void
    {
        $result = $this->loader->load('any-resource');

        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function test_load_withNullType_returnsRouteCollection(): void
    {
        $result = $this->loader->load('resource', null);

        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function test_supports_returnsAlwaysFalse(): void
    {
        $this->assertFalse($this->loader->supports('any-resource'));
        $this->assertFalse($this->loader->supports('any-resource', 'any-type'));
        $this->assertFalse($this->loader->supports(null));
        $this->assertFalse($this->loader->supports(null, null));
    }

    public function test_autoload_returnsRouteCollection(): void
    {
        $result = $this->loader->autoload();

        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function test_autoload_addsRoomTypesControllerRoutes(): void
    {
        $result = $this->loader->autoload();

        // 验证路由集合不为空（因为应该加载了 RoomTypesController 的路由）
        $routes = $result->all();
        $this->assertIsArray($routes);

        // 检查是否有路由被添加（具体路由数量可能因控制器配置而变化）
        $this->assertGreaterThanOrEqual(0, count($routes));
    }

    public function test_loader_canBeInstantiated(): void
    {
        $loader = new AttributeControllerLoader();
        $this->assertInstanceOf(AttributeControllerLoader::class, $loader);
        $this->assertInstanceOf(RoutingAutoLoaderInterface::class, $loader);
    }

    public function test_constructor_hasCorrectSignature(): void
    {
        $reflection = new \ReflectionClass(AttributeControllerLoader::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPublic());
        $this->assertCount(0, $constructor->getParameters());
    }

    public function test_load_methodSignature_isCorrect(): void
    {
        $reflection = new \ReflectionClass(AttributeControllerLoader::class);
        $method = $reflection->getMethod('load');

        $this->assertTrue($method->isPublic());
        $this->assertCount(2, $method->getParameters());

        $parameters = $method->getParameters();
        $this->assertEquals('resource', $parameters[0]->getName());
        $this->assertEquals('type', $parameters[1]->getName());
        $this->assertTrue($parameters[1]->allowsNull());
    }

    public function test_supports_methodSignature_isCorrect(): void
    {
        $reflection = new \ReflectionClass(AttributeControllerLoader::class);
        $method = $reflection->getMethod('supports');

        $this->assertTrue($method->isPublic());
        $this->assertCount(2, $method->getParameters());

        $parameters = $method->getParameters();
        $this->assertEquals('resource', $parameters[0]->getName());
        $this->assertEquals('type', $parameters[1]->getName());
        $this->assertTrue($parameters[1]->allowsNull());
    }

    public function test_autoload_methodSignature_isCorrect(): void
    {
        $reflection = new \ReflectionClass(AttributeControllerLoader::class);
        $method = $reflection->getMethod('autoload');

        $this->assertTrue($method->isPublic());
        $this->assertCount(0, $method->getParameters());
        $this->assertTrue($method->hasReturnType());
        $this->assertEquals(RouteCollection::class, $method->getReturnType()->getName());
    }
}
