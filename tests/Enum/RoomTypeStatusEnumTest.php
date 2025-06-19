<?php

namespace Tourze\HotelProfileBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\HotelProfileBundle\Enum\RoomTypeStatusEnum;

class RoomTypeStatusEnumTest extends TestCase
{
    public function testEnumCases(): void
    {
        $cases = RoomTypeStatusEnum::cases();

        $this->assertCount(2, $cases);
        $this->assertContains(RoomTypeStatusEnum::ACTIVE, $cases);
        $this->assertContains(RoomTypeStatusEnum::DISABLED, $cases);
    }

    public function testEnumValues(): void
    {
        $this->assertSame('active', RoomTypeStatusEnum::ACTIVE->value);
        $this->assertSame('disabled', RoomTypeStatusEnum::DISABLED->value);
    }

    public function testImplementsInterfaces(): void
    {
        $enum = RoomTypeStatusEnum::ACTIVE;

        $this->assertInstanceOf(Labelable::class, $enum);
        $this->assertInstanceOf(Itemable::class, $enum);
        $this->assertInstanceOf(Selectable::class, $enum);
    }

    public function testGetLabelForActive(): void
    {
        $label = RoomTypeStatusEnum::ACTIVE->getLabel();

        $this->assertSame('可用', $label);
    }

    public function testGetLabelForDisabled(): void
    {
        $label = RoomTypeStatusEnum::DISABLED->getLabel();

        $this->assertSame('停用', $label);
    }

    public function testAllCasesHaveLabels(): void
    {
        foreach (RoomTypeStatusEnum::cases() as $case) {
            $label = $case->getLabel();
            $this->assertNotEmpty($label);
        }
    }

    public function testFromString(): void
    {
        $active = RoomTypeStatusEnum::from('active');
        $disabled = RoomTypeStatusEnum::from('disabled');

        $this->assertSame(RoomTypeStatusEnum::ACTIVE, $active);
        $this->assertSame(RoomTypeStatusEnum::DISABLED, $disabled);
    }

    public function testTryFromString(): void
    {
        $active = RoomTypeStatusEnum::tryFrom('active');
        $disabled = RoomTypeStatusEnum::tryFrom('disabled');
        $invalid = RoomTypeStatusEnum::tryFrom('invalid');

        $this->assertSame(RoomTypeStatusEnum::ACTIVE, $active);
        $this->assertSame(RoomTypeStatusEnum::DISABLED, $disabled);
        $this->assertNull($invalid);
    }

    public function testUsesTraits(): void
    {
        // 测试枚举类使用了 ItemTrait 和 SelectTrait
        $reflection = new \ReflectionClass(RoomTypeStatusEnum::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains('Tourze\EnumExtra\ItemTrait', $traits);
        $this->assertContains('Tourze\EnumExtra\SelectTrait', $traits);
    }

    public function testStringRepresentation(): void
    {
        $this->assertSame('active', (string) RoomTypeStatusEnum::ACTIVE->value);
        $this->assertSame('disabled', (string) RoomTypeStatusEnum::DISABLED->value);
    }

    public function testEnumEquality(): void
    {
        $active1 = RoomTypeStatusEnum::ACTIVE;
        $active2 = RoomTypeStatusEnum::ACTIVE;
        $disabled = RoomTypeStatusEnum::DISABLED;

        $this->assertTrue($active1 === $active2);
        $this->assertFalse($active1 === $disabled);
    }

    public function testEnumInSwitch(): void
    {
        $result = match (RoomTypeStatusEnum::ACTIVE) {
            RoomTypeStatusEnum::ACTIVE => 'active_matched',
            RoomTypeStatusEnum::DISABLED => 'disabled_matched',
        };

        $this->assertSame('active_matched', $result);
    }
}
