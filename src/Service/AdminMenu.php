<?php

namespace Tourze\HotelProfileBundle\Service;

use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Entity\RoomType;

/**
 * 应用管理菜单服务
 */
class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private readonly LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if ($item->getChild('酒店管理') === null) {
            $item->addChild('酒店管理');
        }
        $appMenu = $item->getChild('酒店管理');

        $appMenu->addChild('酒店档案')->setUri($this->linkGenerator->getCurdListPage(Hotel::class))->setAttribute('icon', 'fas fa-hotel');
        $appMenu->addChild('房型管理')->setUri($this->linkGenerator->getCurdListPage(RoomType::class))->setAttribute('icon', 'fas fa-bed');
    }
}
