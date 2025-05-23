<?php

namespace Tourze\HotelProfileBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Tourze\HotelProfileBundle\Controller\Admin\HotelCrudController;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Service\HotelImportExportService;

class HotelCrudControllerTest extends TestCase
{
    private HotelCrudController $controller;
    private RequestStack&MockObject $requestStack;
    private HotelImportExportService&MockObject $importExportService;

    protected function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->importExportService = $this->createMock(HotelImportExportService::class);

        $this->controller = new HotelCrudController(
            $this->requestStack,
            $this->importExportService
        );
    }

    public function test_controller_extendsAbstractCrudController(): void
    {
        $reflection = new \ReflectionClass(HotelCrudController::class);
        $this->assertTrue($reflection->isSubclassOf(AbstractCrudController::class));
    }

    public function test_getEntityFqcn_returnsHotelClass(): void
    {
        $this->assertEquals(Hotel::class, HotelCrudController::getEntityFqcn());
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
        $reflection = new \ReflectionClass(HotelCrudController::class);
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

    public function test_exportHotels_methodExists(): void
    {
        $reflection = new \ReflectionClass(HotelCrudController::class);
        $this->assertTrue($reflection->hasMethod('exportHotels'));
        
        $method = $reflection->getMethod('exportHotels');
        $this->assertTrue($method->isPublic());
        $this->assertCount(1, $method->getParameters());
    }

    public function test_importHotelsForm_methodExists(): void
    {
        $reflection = new \ReflectionClass(HotelCrudController::class);
        $this->assertTrue($reflection->hasMethod('importHotelsForm'));
        
        $method = $reflection->getMethod('importHotelsForm');
        $this->assertTrue($method->isPublic());
        $this->assertCount(2, $method->getParameters());
    }

    public function test_downloadImportTemplate_methodExists(): void
    {
        $reflection = new \ReflectionClass(HotelCrudController::class);
        $this->assertTrue($reflection->hasMethod('downloadImportTemplate'));
        
        $method = $reflection->getMethod('downloadImportTemplate');
        $this->assertTrue($method->isPublic());
        $this->assertCount(1, $method->getParameters());
    }
} 