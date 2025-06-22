<?php

namespace Tourze\HotelProfileBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;

class HotelStatusEnumTest extends TestCase
{
    public function testEnumCases(): void
    {
        $cases = HotelStatusEnum::cases();

        $this->assertCount(2, $cases);
        $this->assertContains(HotelStatusEnum::OPERATING, $cases);
        $this->assertContains(HotelStatusEnum::SUSPENDED, $cases);
    }

    public function testEnumValues(): void
    {
        $this->assertSame('operating', HotelStatusEnum::OPERATING->value);
        $this->assertSame('suspended', HotelStatusEnum::SUSPENDED->value);
    }

    public function testImplementsInterfaces(): void
    {
        $enum = HotelStatusEnum::OPERATING;

        $this->assertInstanceOf(Labelable::class, $enum);
        $this->assertInstanceOf(Itemable::class, $enum);
        $this->assertInstanceOf(Selectable::class, $enum);
    }

    public function testGetLabelForOperating(): void
    {
        $label = HotelStatusEnum::OPERATING->getLabel();

        $this->assertSame('运营中', $label);
    }

    public function testGetLabelForSuspended(): void
    {
        $label = HotelStatusEnum::SUSPENDED->getLabel();

        $this->assertSame('暂停合作', $label);
    }

    public function testAllCasesHaveLabels(): void
    {
        foreach (HotelStatusEnum::cases() as $case) {
            $label = $case->getLabel();
            $this->assertNotEmpty($label);
        }
    }

    public function testFromString(): void
    {
        $operating = HotelStatusEnum::from('operating');
        $suspended = HotelStatusEnum::from('suspended');

        $this->assertSame(HotelStatusEnum::OPERATING, $operating);
        $this->assertSame(HotelStatusEnum::SUSPENDED, $suspended);
    }

    public function testTryFromString(): void
    {
        $operating = HotelStatusEnum::tryFrom('operating');
        $suspended = HotelStatusEnum::tryFrom('suspended');
        $invalid = HotelStatusEnum::tryFrom('invalid');

        $this->assertSame(HotelStatusEnum::OPERATING, $operating);
        $this->assertSame(HotelStatusEnum::SUSPENDED, $suspended);
        $this->assertNull($invalid);
    }

    public function testUsesTraits(): void
    {
        // 测试枚举类使用了 ItemTrait 和 SelectTrait
        $reflection = new \ReflectionClass(HotelStatusEnum::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains('Tourze\EnumExtra\ItemTrait', $traits);
        $this->assertContains('Tourze\EnumExtra\SelectTrait', $traits);
    }

    public function testStringRepresentation(): void
    {
        $this->assertSame('operating', (string) HotelStatusEnum::OPERATING->value);
        $this->assertSame('suspended', (string) HotelStatusEnum::SUSPENDED->value);
    }

    public function testEnumEquality(): void
    {
        $operating1 = HotelStatusEnum::OPERATING;
        $suspended = HotelStatusEnum::SUSPENDED;

        // 测试枚举值的比较
        $this->assertSame($operating1, HotelStatusEnum::OPERATING);
        $this->assertNotSame($operating1, $suspended);
    }

    /**
     * @dataProvider statusMatchProvider
     */
    public function testEnumInSwitch(HotelStatusEnum $status, string $expected): void
    {
        $result = match ($status) {
            HotelStatusEnum::OPERATING => 'operating_matched',
            HotelStatusEnum::SUSPENDED => 'suspended_matched',
        };

        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{HotelStatusEnum, string}>
     */
    public function statusMatchProvider(): array
    {
        return [
            'operating' => [HotelStatusEnum::OPERATING, 'operating_matched'],
            'suspended' => [HotelStatusEnum::SUSPENDED, 'suspended_matched'],
        ];
    }
}
