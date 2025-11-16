<?php

declare(strict_types=1);

namespace Tourze\HotelProfileBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 房型状态枚举
 */
enum RoomTypeStatusEnum: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case ACTIVE = 'active';
    case DISABLED = 'disabled';

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => '可用',
            self::DISABLED => '停用',
        };
    }
}
