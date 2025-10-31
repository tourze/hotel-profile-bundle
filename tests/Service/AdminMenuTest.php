<?php

namespace Tourze\HotelProfileBundle\Tests\Service;

use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\HotelProfileBundle\Service\AdminMenu;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        $linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $linkGenerator->method('getCurdListPage')->willReturn('/admin/entity/list');

        self::getContainer()->set(LinkGeneratorInterface::class, $linkGenerator);
    }

    private function getAdminMenu(): AdminMenu
    {
        return self::getService(AdminMenu::class);
    }

    public function testInvokeCreatesHotelManagementMenu(): void
    {
        $menuFactory = new MenuFactory();
        $rootMenu = new MenuItem('root', $menuFactory);

        ($this->getAdminMenu())($rootMenu);

        $hotelManagementMenu = $rootMenu->getChild('酒店管理');
        $this->assertNotNull($hotelManagementMenu);

        $hotelProfileItem = $hotelManagementMenu->getChild('酒店档案');
        $this->assertNotNull($hotelProfileItem);
        $this->assertNotEmpty($hotelProfileItem->getUri());
        $this->assertEquals('fas fa-hotel', $hotelProfileItem->getAttribute('icon'));

        $roomTypeItem = $hotelManagementMenu->getChild('房型管理');
        $this->assertNotNull($roomTypeItem);
        $this->assertNotEmpty($roomTypeItem->getUri());
        $this->assertEquals('fas fa-bed', $roomTypeItem->getAttribute('icon'));
    }

    public function testInvokeWithExistingHotelManagementMenu(): void
    {
        $menuFactory = new MenuFactory();
        $rootMenu = new MenuItem('root', $menuFactory);
        $existingHotelMenu = new MenuItem('酒店管理', $menuFactory);
        $rootMenu->addChild($existingHotelMenu);

        $adminMenu = $this->getAdminMenu();
        $adminMenu($rootMenu);

        $hotelManagementMenu = $rootMenu->getChild('酒店管理');
        $this->assertSame($existingHotelMenu, $hotelManagementMenu);

        $this->assertNotNull($hotelManagementMenu->getChild('酒店档案'));
        $this->assertNotNull($hotelManagementMenu->getChild('房型管理'));
    }
}
