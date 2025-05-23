<?php

namespace Tourze\HotelProfileBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;

class HotelStatusEnumTest extends TestCase
{
    public function test_enumValues_areCorrect(): void
    {
        $this->assertEquals('operating', HotelStatusEnum::OPERATING->value);
        $this->assertEquals('suspended', HotelStatusEnum::SUSPENDED->value);
    }

    public function test_getLabel_returnsCorrectLabels(): void
    {
        $this->assertEquals('运营中', HotelStatusEnum::OPERATING->getLabel());
        $this->assertEquals('暂停合作', HotelStatusEnum::SUSPENDED->getLabel());
    }

    public function test_allCases_containsAllEnumValues(): void
    {
        $cases = HotelStatusEnum::cases();
        
        $this->assertCount(2, $cases);
        $this->assertContains(HotelStatusEnum::OPERATING, $cases);
        $this->assertContains(HotelStatusEnum::SUSPENDED, $cases);
    }

    public function test_fromValue_createsCorrectEnum(): void
    {
        $this->assertEquals(HotelStatusEnum::OPERATING, HotelStatusEnum::from('operating'));
        $this->assertEquals(HotelStatusEnum::SUSPENDED, HotelStatusEnum::from('suspended'));
    }

    public function test_fromValue_withInvalidValue_throwsException(): void
    {
        $this->expectException(\ValueError::class);
        HotelStatusEnum::from('invalid');
    }

    public function test_implements_expectedInterfaces(): void
    {
        $reflection = new \ReflectionClass(HotelStatusEnum::class);
        
        $this->assertTrue($reflection->implementsInterface(\Tourze\EnumExtra\Labelable::class));
        $this->assertTrue($reflection->implementsInterface(\Tourze\EnumExtra\Itemable::class));
        $this->assertTrue($reflection->implementsInterface(\Tourze\EnumExtra\Selectable::class));
    }

    public function test_usesCorrectTraits(): void
    {
        $reflection = new \ReflectionClass(HotelStatusEnum::class);
        $traits = $reflection->getTraitNames();
        
        $this->assertContains(\Tourze\EnumExtra\ItemTrait::class, $traits);
        $this->assertContains(\Tourze\EnumExtra\SelectTrait::class, $traits);
    }

    public function test_enumIsBackedByString(): void
    {
        $reflection = new \ReflectionEnum(HotelStatusEnum::class);
        $this->assertTrue($reflection->isBacked());
        $this->assertEquals('string', $reflection->getBackingType()->getName());
    }

    public function test_getLabelMethod_exists(): void
    {
        $reflection = new \ReflectionClass(HotelStatusEnum::class);
        $this->assertTrue($reflection->hasMethod('getLabel'));
        
        $method = $reflection->getMethod('getLabel');
        $this->assertTrue($method->isPublic());
        $this->assertEquals('string', $method->getReturnType()->getName());
    }
} 