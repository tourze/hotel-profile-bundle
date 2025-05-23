<?php

namespace Tourze\HotelProfileBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\HotelProfileBundle\Repository\RoomTypeRepository;

class RoomTypeRepositoryTest extends TestCase
{
    public function test_repositoryExtendsServiceEntityRepository(): void
    {
        $reflection = new \ReflectionClass(RoomTypeRepository::class);
        $this->assertTrue($reflection->isSubclassOf(\Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository::class));
    }

    public function test_repositoryHasCorrectMethods(): void
    {
        $reflection = new \ReflectionClass(RoomTypeRepository::class);
        $this->assertTrue($reflection->hasMethod('save'));
        $this->assertTrue($reflection->hasMethod('remove'));
        $this->assertTrue($reflection->hasMethod('findByHotelId'));
        $this->assertTrue($reflection->hasMethod('findByNameAndHotelId'));
        $this->assertTrue($reflection->hasMethod('findActiveRoomTypes'));
    }

    public function test_saveMethod_hasCorrectSignature(): void
    {
        $reflection = new \ReflectionClass(RoomTypeRepository::class);
        $method = $reflection->getMethod('save');
        
        $this->assertTrue($method->isPublic());
        $this->assertCount(2, $method->getParameters());
        
        $parameters = $method->getParameters();
        $this->assertEquals('entity', $parameters[0]->getName());
        $this->assertEquals('flush', $parameters[1]->getName());
        $this->assertTrue($parameters[1]->hasType());
        $this->assertEquals('bool', $parameters[1]->getType()->getName());
        $this->assertTrue($parameters[1]->isDefaultValueAvailable());
        $this->assertFalse($parameters[1]->getDefaultValue());
    }

    public function test_removeMethod_hasCorrectSignature(): void
    {
        $reflection = new \ReflectionClass(RoomTypeRepository::class);
        $method = $reflection->getMethod('remove');
        
        $this->assertTrue($method->isPublic());
        $this->assertCount(2, $method->getParameters());
        
        $parameters = $method->getParameters();
        $this->assertEquals('entity', $parameters[0]->getName());
        $this->assertEquals('flush', $parameters[1]->getName());
        $this->assertTrue($parameters[1]->hasType());
        $this->assertEquals('bool', $parameters[1]->getType()->getName());
        $this->assertTrue($parameters[1]->isDefaultValueAvailable());
        $this->assertFalse($parameters[1]->getDefaultValue());
    }

    public function test_findByHotelIdMethod_hasCorrectSignature(): void
    {
        $reflection = new \ReflectionClass(RoomTypeRepository::class);
        $method = $reflection->getMethod('findByHotelId');
        
        $this->assertTrue($method->isPublic());
        $this->assertCount(1, $method->getParameters());
        
        $parameters = $method->getParameters();
        $this->assertEquals('hotelId', $parameters[0]->getName());
        $this->assertTrue($parameters[0]->hasType());
        $this->assertEquals('int', $parameters[0]->getType()->getName());
    }

    public function test_findByNameAndHotelIdMethod_hasCorrectSignature(): void
    {
        $reflection = new \ReflectionClass(RoomTypeRepository::class);
        $method = $reflection->getMethod('findByNameAndHotelId');
        
        $this->assertTrue($method->isPublic());
        $this->assertCount(2, $method->getParameters());
        
        $parameters = $method->getParameters();
        $this->assertEquals('name', $parameters[0]->getName());
        $this->assertTrue($parameters[0]->hasType());
        $this->assertEquals('string', $parameters[0]->getType()->getName());
        
        $this->assertEquals('hotelId', $parameters[1]->getName());
        $this->assertTrue($parameters[1]->hasType());
        $this->assertEquals('int', $parameters[1]->getType()->getName());
    }

    public function test_findActiveRoomTypesMethod_hasCorrectSignature(): void
    {
        $reflection = new \ReflectionClass(RoomTypeRepository::class);
        $method = $reflection->getMethod('findActiveRoomTypes');
        
        $this->assertTrue($method->isPublic());
        $this->assertCount(0, $method->getParameters());
    }

    public function test_classDocumentation_isCorrect(): void
    {
        $reflection = new \ReflectionClass(RoomTypeRepository::class);
        $docComment = $reflection->getDocComment();
        
        $this->assertStringContainsString('房型仓库类', $docComment);
        $this->assertStringContainsString('@extends ServiceEntityRepository<RoomType>', $docComment);
        $this->assertStringContainsString('@method RoomType|null find(', $docComment);
        $this->assertStringContainsString('@method RoomType[]    findAll()', $docComment);
    }
} 