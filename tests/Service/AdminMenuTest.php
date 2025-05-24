<?php

namespace Tourze\HotelProfileBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Entity\RoomType;
use Tourze\HotelProfileBundle\Service\AdminMenu;

class AdminMenuTest extends TestCase
{
    private AdminMenu $adminMenu;
    private LinkGeneratorInterface&MockObject $linkGenerator;
    private ItemInterface&MockObject $rootItem;
    private ItemInterface&MockObject $hotelMenuItem;

    protected function setUp(): void
    {
        $this->linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $this->rootItem = $this->createMock(ItemInterface::class);
        $this->hotelMenuItem = $this->createMock(ItemInterface::class);
        
        $this->adminMenu = new AdminMenu($this->linkGenerator);
    }

    public function test_implements_menuProviderInterface(): void
    {
        $reflection = new \ReflectionClass(AdminMenu::class);
        $this->assertTrue($reflection->implementsInterface(MenuProviderInterface::class));
    }

    public function test_invoke_createsHotelManagementMenu(): void
    {
        $this->rootItem
            ->expects($this->exactly(2))
            ->method('getChild')
            ->with('酒店管理')
            ->willReturnOnConsecutiveCalls(null, $this->hotelMenuItem);

        $this->rootItem
            ->expects($this->once())
            ->method('addChild')
            ->with('酒店管理')
            ->willReturn($this->hotelMenuItem);

        $this->linkGenerator
            ->expects($this->exactly(2))
            ->method('getCurdListPage')
            ->willReturnCallback(function($entityClass) {
                return match ($entityClass) {
                    Hotel::class => '/admin?crudController=Hotel',
                    RoomType::class => '/admin?crudController=RoomType',
                    default => null
                };
            });

        $hotelArchiveItem = $this->createMock(ItemInterface::class);
        $roomTypeItem = $this->createMock(ItemInterface::class);

        $this->hotelMenuItem
            ->expects($this->exactly(2))
            ->method('addChild')
            ->willReturnCallback(function($menuName) use ($hotelArchiveItem, $roomTypeItem) {
                return match ($menuName) {
                    '酒店档案' => $hotelArchiveItem,
                    '房型管理' => $roomTypeItem,
                    default => null
                };
            });

        $hotelArchiveItem
            ->expects($this->once())
            ->method('setUri')
            ->with('/admin?crudController=Hotel')
            ->willReturnSelf();

        $hotelArchiveItem
            ->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-hotel')
            ->willReturnSelf();

        $roomTypeItem
            ->expects($this->once())
            ->method('setUri')
            ->with('/admin?crudController=RoomType')
            ->willReturnSelf();

        $roomTypeItem
            ->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-bed')
            ->willReturnSelf();

        ($this->adminMenu)($this->rootItem);
    }

    public function test_invoke_withExistingHotelMenu_usesExistingMenu(): void
    {
        $this->rootItem
            ->expects($this->exactly(2))
            ->method('getChild')
            ->with('酒店管理')
            ->willReturn($this->hotelMenuItem);

        $this->rootItem
            ->expects($this->never())
            ->method('addChild');

        $this->linkGenerator
            ->expects($this->exactly(2))
            ->method('getCurdListPage')
            ->willReturnCallback(function($entityClass) {
                return match ($entityClass) {
                    Hotel::class => '/admin?crudController=Hotel',
                    RoomType::class => '/admin?crudController=RoomType',
                    default => null
                };
            });

        $hotelArchiveItem = $this->createMock(ItemInterface::class);
        $roomTypeItem = $this->createMock(ItemInterface::class);

        $this->hotelMenuItem
            ->expects($this->exactly(2))
            ->method('addChild')
            ->willReturnCallback(function($menuName) use ($hotelArchiveItem, $roomTypeItem) {
                return match ($menuName) {
                    '酒店档案' => $hotelArchiveItem,
                    '房型管理' => $roomTypeItem,
                    default => null
                };
            });

        $hotelArchiveItem
            ->expects($this->once())
            ->method('setUri')
            ->with('/admin?crudController=Hotel')
            ->willReturnSelf();

        $hotelArchiveItem
            ->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-hotel')
            ->willReturnSelf();

        $roomTypeItem
            ->expects($this->once())
            ->method('setUri')
            ->with('/admin?crudController=RoomType')
            ->willReturnSelf();

        $roomTypeItem
            ->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-bed')
            ->willReturnSelf();

        ($this->adminMenu)($this->rootItem);
    }

    public function test_adminMenu_hasCorrectDependency(): void
    {
        $this->assertInstanceOf(LinkGeneratorInterface::class, 
            $this->getObjectAttribute($this->adminMenu, 'linkGenerator'));
    }

    public function test_adminMenu_hasCorrectNamespace(): void
    {
        $reflection = new \ReflectionClass(AdminMenu::class);
        $this->assertEquals('Tourze\HotelProfileBundle\Service', $reflection->getNamespaceName());
    }

    public function test_constructor_hasCorrectSignature(): void
    {
        $reflection = new \ReflectionClass(AdminMenu::class);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPublic());
        $this->assertCount(1, $constructor->getParameters());
        
        $parameter = $constructor->getParameters()[0];
        $this->assertEquals('linkGenerator', $parameter->getName());
        $this->assertTrue($parameter->hasType());
        $this->assertEquals(LinkGeneratorInterface::class, $parameter->getType()->getName());
    }

    public function test_adminMenu_canBeInstantiated(): void
    {
        $linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $adminMenu = new AdminMenu($linkGenerator);
        
        $this->assertInstanceOf(AdminMenu::class, $adminMenu);
        $this->assertInstanceOf(MenuProviderInterface::class, $adminMenu);
    }

    public function test_adminMenu_isInvokable(): void
    {
        $reflection = new \ReflectionClass(AdminMenu::class);
        $this->assertTrue($reflection->hasMethod('__invoke'));
        
        $method = $reflection->getMethod('__invoke');
        $this->assertTrue($method->isPublic());
        $this->assertCount(1, $method->getParameters());
        
        $parameter = $method->getParameters()[0];
        $this->assertEquals('item', $parameter->getName());
        $this->assertTrue($parameter->hasType());
        $this->assertEquals(ItemInterface::class, $parameter->getType()->getName());
    }

    private function getObjectAttribute(object $object, string $attributeName): mixed
    {
        $reflection = new \ReflectionObject($object);
        $property = $reflection->getProperty($attributeName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
} 