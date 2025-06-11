<?php

namespace Tourze\HotelProfileBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\HotelProfileBundle\Service\AdminMenu;

class AdminMenuTest extends TestCase
{
    private AdminMenu $adminMenu;
    private LinkGeneratorInterface $linkGenerator;

    protected function setUp(): void
    {
        $this->linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $this->adminMenu = new AdminMenu($this->linkGenerator);
    }

    public function testImplementsMenuProviderInterface(): void
    {
        $this->assertInstanceOf(MenuProviderInterface::class, $this->adminMenu);
    }

    public function testConstructorAcceptsLinkGenerator(): void
    {
        $linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $adminMenu = new AdminMenu($linkGenerator);

        $this->assertInstanceOf(AdminMenu::class, $adminMenu);
    }

    public function testServiceIsCallable(): void
    {
        $this->assertTrue(is_callable($this->adminMenu));
    }

    public function testHasInvokeMethod(): void
    {
        $this->assertTrue(method_exists($this->adminMenu, '__invoke'));
    }

    public function testLinkGeneratorPropertyExists(): void
    {
        // 通过反射验证 linkGenerator 属性存在
        $reflection = new \ReflectionClass($this->adminMenu);
        $this->assertTrue($reflection->hasProperty('linkGenerator'));
    }

    public function testLinkGeneratorPropertyIsReadonly(): void
    {
        // 通过反射验证 linkGenerator 属性是 readonly
        $reflection = new \ReflectionClass($this->adminMenu);
        $property = $reflection->getProperty('linkGenerator');
        $this->assertTrue($property->isReadOnly());
    }

    public function testLinkGeneratorPropertyIsPrivate(): void
    {
        // 通过反射验证 linkGenerator 属性是 private
        $reflection = new \ReflectionClass($this->adminMenu);
        $property = $reflection->getProperty('linkGenerator');
        $this->assertTrue($property->isPrivate());
    }
}
