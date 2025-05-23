<?php

namespace Tourze\HotelProfileBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\HotelProfileBundle\Repository\HotelRepository;

class HotelRepositoryTest extends TestCase
{
    public function test_repositoryExtendsServiceEntityRepository(): void
    {
        $reflection = new \ReflectionClass(HotelRepository::class);
        $this->assertTrue($reflection->isSubclassOf(\Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository::class));
    }

    public function test_repositoryHasCorrectEntityClass(): void
    {
        $reflection = new \ReflectionClass(HotelRepository::class);
        $this->assertTrue($reflection->hasMethod('save'));
        $this->assertTrue($reflection->hasMethod('remove'));
        $this->assertTrue($reflection->hasMethod('findByName'));
        $this->assertTrue($reflection->hasMethod('findByStarLevel'));
        $this->assertTrue($reflection->hasMethod('findOperatingHotels'));
    }

    public function test_saveMethod_hasCorrectSignature(): void
    {
        $reflection = new \ReflectionClass(HotelRepository::class);
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
        $reflection = new \ReflectionClass(HotelRepository::class);
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

    public function test_findByNameMethod_hasCorrectSignature(): void
    {
        $reflection = new \ReflectionClass(HotelRepository::class);
        $method = $reflection->getMethod('findByName');
        
        $this->assertTrue($method->isPublic());
        $this->assertCount(1, $method->getParameters());
        
        $parameters = $method->getParameters();
        $this->assertEquals('name', $parameters[0]->getName());
        $this->assertTrue($parameters[0]->hasType());
        $this->assertEquals('string', $parameters[0]->getType()->getName());
    }

    public function test_findByStarLevelMethod_hasCorrectSignature(): void
    {
        $reflection = new \ReflectionClass(HotelRepository::class);
        $method = $reflection->getMethod('findByStarLevel');
        
        $this->assertTrue($method->isPublic());
        $this->assertCount(1, $method->getParameters());
        
        $parameters = $method->getParameters();
        $this->assertEquals('starLevel', $parameters[0]->getName());
        $this->assertTrue($parameters[0]->hasType());
        $this->assertEquals('int', $parameters[0]->getType()->getName());
    }

    public function test_findOperatingHotelsMethod_hasCorrectSignature(): void
    {
        $reflection = new \ReflectionClass(HotelRepository::class);
        $method = $reflection->getMethod('findOperatingHotels');
        
        $this->assertTrue($method->isPublic());
        $this->assertCount(0, $method->getParameters());
    }

    public function test_classDocumentation_isCorrect(): void
    {
        $reflection = new \ReflectionClass(HotelRepository::class);
        $docComment = $reflection->getDocComment();
        
        $this->assertStringContainsString('酒店仓库类', $docComment);
        $this->assertStringContainsString('@extends ServiceEntityRepository<Hotel>', $docComment);
        $this->assertStringContainsString('@method Hotel|null find(', $docComment);
        $this->assertStringContainsString('@method Hotel[]    findAll()', $docComment);
    }
} 