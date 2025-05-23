<?php

namespace Tourze\HotelProfileBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\HotelProfileBundle\Enum\RoomTypeStatusEnum;

class RoomTypeStatusEnumTest extends TestCase
{
    public function test_enumValues_areCorrect(): void
    {
        $this->assertEquals('active', RoomTypeStatusEnum::ACTIVE->value);
        $this->assertEquals('disabled', RoomTypeStatusEnum::DISABLED->value);
    }

    public function test_getLabel_returnsCorrectLabels(): void
    {
        $this->assertEquals('可用', RoomTypeStatusEnum::ACTIVE->getLabel());
        $this->assertEquals('停用', RoomTypeStatusEnum::DISABLED->getLabel());
    }

    public function test_allCases_containsAllEnumValues(): void
    {
        $cases = RoomTypeStatusEnum::cases();
        
        $this->assertCount(2, $cases);
        $this->assertContains(RoomTypeStatusEnum::ACTIVE, $cases);
        $this->assertContains(RoomTypeStatusEnum::DISABLED, $cases);
    }

    public function test_fromValue_createsCorrectEnum(): void
    {
        $this->assertEquals(RoomTypeStatusEnum::ACTIVE, RoomTypeStatusEnum::from('active'));
        $this->assertEquals(RoomTypeStatusEnum::DISABLED, RoomTypeStatusEnum::from('disabled'));
    }

    public function test_fromValue_withInvalidValue_throwsException(): void
    {
        $this->expectException(\ValueError::class);
        RoomTypeStatusEnum::from('invalid');
    }

    public function test_implements_expectedInterfaces(): void
    {
        $reflection = new \ReflectionClass(RoomTypeStatusEnum::class);
        
        $this->assertTrue($reflection->implementsInterface(\Tourze\EnumExtra\Labelable::class));
        $this->assertTrue($reflection->implementsInterface(\Tourze\EnumExtra\Itemable::class));
        $this->assertTrue($reflection->implementsInterface(\Tourze\EnumExtra\Selectable::class));
    }

    public function test_usesCorrectTraits(): void
    {
        $reflection = new \ReflectionClass(RoomTypeStatusEnum::class);
        $traits = $reflection->getTraitNames();
        
        $this->assertContains(\Tourze\EnumExtra\ItemTrait::class, $traits);
        $this->assertContains(\Tourze\EnumExtra\SelectTrait::class, $traits);
    }

    public function test_enumIsBackedByString(): void
    {
        $reflection = new \ReflectionEnum(RoomTypeStatusEnum::class);
        $this->assertTrue($reflection->isBacked());
        $this->assertEquals('string', $reflection->getBackingType()->getName());
    }

    public function test_getLabelMethod_exists(): void
    {
        $reflection = new \ReflectionClass(RoomTypeStatusEnum::class);
        $this->assertTrue($reflection->hasMethod('getLabel'));
        
        $method = $reflection->getMethod('getLabel');
        $this->assertTrue($method->isPublic());
        $this->assertEquals('string', $method->getReturnType()->getName());
    }
} 