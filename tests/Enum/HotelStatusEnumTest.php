<?php

namespace Tourze\HotelProfileBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(HotelStatusEnum::class)]
final class HotelStatusEnumTest extends AbstractEnumTestCase
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

    #[DataProvider('enumValueAndLabelProvider')]
    public function testEnumValueAndLabel(HotelStatusEnum $enum, string $expectedValue, string $expectedLabel): void
    {
        $this->assertSame($expectedValue, $enum->value);
        $this->assertSame($expectedLabel, $enum->getLabel());
    }

    /**
     * @return array<string, array{HotelStatusEnum, string, string}>
     */
    public static function enumValueAndLabelProvider(): array
    {
        return [
            'operating' => [HotelStatusEnum::OPERATING, 'operating', '运营中'],
            'suspended' => [HotelStatusEnum::SUSPENDED, 'suspended', '暂停合作'],
        ];
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

    public function testFromThrowsExceptionForInvalidValue(): void
    {
        $this->expectException(\ValueError::class);
        HotelStatusEnum::from('invalid');
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        $result = HotelStatusEnum::tryFrom('nonexistent');
        $this->assertNull($result);
    }

    public function testValueUniqueness(): void
    {
        $values = [];
        foreach (HotelStatusEnum::cases() as $case) {
            $values[] = $case->value;
        }

        $uniqueValues = array_unique($values);
        $this->assertCount(count($values), $uniqueValues, 'All enum values must be unique');
    }

    public function testLabelUniqueness(): void
    {
        $labels = [];
        foreach (HotelStatusEnum::cases() as $case) {
            $labels[] = $case->getLabel();
        }

        $uniqueLabels = array_unique($labels);
        $this->assertCount(count($labels), $uniqueLabels, 'All enum labels must be unique');
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

        // 测试枚举值的比较
        $this->assertSame($operating1, HotelStatusEnum::OPERATING);

        // 测试枚举值的数量
        $this->assertCount(2, HotelStatusEnum::cases());
    }

    #[DataProvider('statusMatchProvider')]
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
    public static function statusMatchProvider(): array
    {
        return [
            'operating' => [HotelStatusEnum::OPERATING, 'operating_matched'],
            'suspended' => [HotelStatusEnum::SUSPENDED, 'suspended_matched'],
        ];
    }

    public function testToArray(): void
    {
        $array = HotelStatusEnum::OPERATING->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('value', $array);
        $this->assertArrayHasKey('label', $array);
        $this->assertEquals('operating', $array['value']);
        $this->assertEquals('运营中', $array['label']);
    }
}
