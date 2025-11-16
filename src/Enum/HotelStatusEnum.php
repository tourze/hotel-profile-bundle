<?php

declare(strict_types=1);

namespace Tourze\HotelProfileBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 酒店状态枚举
 */
enum HotelStatusEnum: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case OPERATING = 'operating';
    case SUSPENDED = 'suspended';

    public function getLabel(): string
    {
        return match ($this) {
            self::OPERATING => '运营中',
            self::SUSPENDED => '暂停合作',
        };
    }
}
