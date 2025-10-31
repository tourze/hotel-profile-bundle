<?php

namespace Tourze\HotelProfileBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\HotelProfileBundle\Enum\RoomTypeStatusEnum;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(RoomTypeStatusEnum::class)]
final class RoomTypeStatusEnumTest extends AbstractEnumTestCase
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

    #[DataProvider('enumValueAndLabelProvider')]
    public function testEnumValueAndLabel(RoomTypeStatusEnum $enum, string $expectedValue, string $expectedLabel): void
    {
        $this->assertSame($expectedValue, $enum->value);
        $this->assertSame($expectedLabel, $enum->getLabel());
    }

    /**
     * @return array<string, array{RoomTypeStatusEnum, string, string}>
     */
    public static function enumValueAndLabelProvider(): array
    {
        return [
            'active' => [RoomTypeStatusEnum::ACTIVE, 'active', '可用'],
            'disabled' => [RoomTypeStatusEnum::DISABLED, 'disabled', '停用'],
        ];
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

    public function testFromThrowsExceptionForInvalidValue(): void
    {
        $this->expectException(\ValueError::class);
        RoomTypeStatusEnum::from('invalid');
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        $result = RoomTypeStatusEnum::tryFrom('nonexistent');
        $this->assertNull($result);
    }

    public function testValueUniqueness(): void
    {
        $values = [];
        foreach (RoomTypeStatusEnum::cases() as $case) {
            $values[] = $case->value;
        }

        $uniqueValues = array_unique($values);
        $this->assertCount(count($values), $uniqueValues, 'All enum values must be unique');
    }

    public function testLabelUniqueness(): void
    {
        $labels = [];
        foreach (RoomTypeStatusEnum::cases() as $case) {
            $labels[] = $case->getLabel();
        }

        $uniqueLabels = array_unique($labels);
        $this->assertCount(count($labels), $uniqueLabels, 'All enum labels must be unique');
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

        // 测试枚举值的比较
        $this->assertSame($active1, RoomTypeStatusEnum::ACTIVE);

        // 测试枚举值的数量
        $this->assertCount(2, RoomTypeStatusEnum::cases());
    }

    #[DataProvider('statusMatchProvider')]
    public function testEnumInSwitch(RoomTypeStatusEnum $status, string $expected): void
    {
        $result = match ($status) {
            RoomTypeStatusEnum::ACTIVE => 'active_matched',
            RoomTypeStatusEnum::DISABLED => 'disabled_matched',
        };

        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{RoomTypeStatusEnum, string}>
     */
    public static function statusMatchProvider(): array
    {
        return [
            'active' => [RoomTypeStatusEnum::ACTIVE, 'active_matched'],
            'disabled' => [RoomTypeStatusEnum::DISABLED, 'disabled_matched'],
        ];
    }

    public function testToArray(): void
    {
        $array = RoomTypeStatusEnum::ACTIVE->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('value', $array);
        $this->assertArrayHasKey('label', $array);
        $this->assertEquals('active', $array['value']);
        $this->assertEquals('可用', $array['label']);
    }
}
