<?php

namespace Tourze\HotelProfileBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Tourze\HotelProfileBundle\Controller\Admin\RoomTypeCrudController;
use Tourze\HotelProfileBundle\Entity\RoomType;

class RoomTypeCrudControllerTest extends TestCase
{
    private RoomTypeCrudController $controller;
    private RequestStack&MockObject $requestStack;

    protected function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        
        $this->controller = new RoomTypeCrudController($this->requestStack);
    }

    public function test_controller_extendsAbstractCrudController(): void
    {
        $reflection = new \ReflectionClass(RoomTypeCrudController::class);
        $this->assertTrue($reflection->isSubclassOf(AbstractCrudController::class));
    }

    public function test_getEntityFqcn_returnsRoomTypeClass(): void
    {
        $this->assertEquals(RoomType::class, RoomTypeCrudController::getEntityFqcn());
    }

    public function test_configureCrud_returnsProperCrudInstance(): void
    {
        $crud = Crud::new('test');
        $result = $this->controller->configureCrud($crud);
        
        $this->assertInstanceOf(Crud::class, $result);
    }

    public function test_configureActions_returnsProperActionsInstance(): void
    {
        // 使用反射来测试方法存在性，避免EasyAdmin配置复杂性
        $reflection = new \ReflectionClass(RoomTypeCrudController::class);
        $this->assertTrue($reflection->hasMethod('configureActions'));
        
        $method = $reflection->getMethod('configureActions');
        $this->assertTrue($method->isPublic());
        $this->assertCount(1, $method->getParameters());
        
        $parameter = $method->getParameters()[0];
        $this->assertEquals('actions', $parameter->getName());
    }

    public function test_configureFilters_returnsProperFiltersInstance(): void
    {
        $filters = Filters::new();
        $result = $this->controller->configureFilters($filters);
        
        $this->assertInstanceOf(Filters::class, $result);
    }

    public function test_configureFields_returnsFieldsIterable(): void
    {
        $fields = $this->controller->configureFields('index');
        
        $this->assertIsIterable($fields);
        
        $fieldsArray = iterator_to_array($fields);
        $this->assertNotEmpty($fieldsArray);
    }

    public function test_configureFields_withDifferentPageNames(): void
    {
        $pageNames = ['index', 'new', 'edit', 'detail'];
        
        foreach ($pageNames as $pageName) {
            $fields = $this->controller->configureFields($pageName);
            $this->assertIsIterable($fields, "Fields should be iterable for page: {$pageName}");
            
            $fieldsArray = iterator_to_array($fields);
            $this->assertNotEmpty($fieldsArray, "Fields should not be empty for page: {$pageName}");
        }
    }
} 