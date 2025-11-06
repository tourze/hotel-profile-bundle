<?php

namespace Tourze\HotelProfileBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;
use Tourze\HotelProfileBundle\Service\HotelImportExportService;

#[AdminCrud(routePath: '/hotel-profile/hotel', routeName: 'hotel_profile_hotel')]
final class HotelCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly HotelImportExportService $importExportService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Hotel::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('酒店')
            ->setEntityLabelInPlural('酒店列表')
            ->setPageTitle('index', '酒店管理')
            ->setPageTitle('new', '添加酒店')
            ->setPageTitle('edit', fn (Hotel $hotel) => sprintf('编辑酒店: %s', $hotel->getName()))
            ->setPageTitle('detail', fn (Hotel $hotel) => sprintf('酒店详情: %s', $hotel->getName()))
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['name', 'address', 'contactPerson', 'phone'])
            ->showEntityActionsInlined()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $export = Action::new('exportHotels', '导出Excel')
            ->linkToCrudAction('exportHotels')
            ->createAsGlobalAction()
            ->setCssClass('btn btn-success')
            ->setIcon('fa fa-download')
        ;

        $import = Action::new('importHotels', '批量导入')
            ->linkToCrudAction('importHotelsForm')
            ->createAsGlobalAction()
            ->setCssClass('btn btn-primary')
            ->setIcon('fa fa-upload')
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $export)
            ->add(Crud::PAGE_INDEX, $import)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', '酒店名称'))
            ->add(ChoiceFilter::new('starLevel', '星级')->setChoices([
                '一星级' => 1,
                '二星级' => 2,
                '三星级' => 3,
                '四星级' => 4,
                '五星级' => 5,
            ]))
            ->add(ChoiceFilter::new('status', '状态')->setChoices([
                '运营中' => HotelStatusEnum::OPERATING->value,
                '暂停合作' => HotelStatusEnum::SUSPENDED->value,
            ]))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $request = $this->requestStack->getCurrentRequest();

        yield FormField::addFieldset('基本信息')->setIcon('fa fa-hotel');

        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield TextField::new('name', '酒店名称')
            ->setColumns(6)
            ->setRequired(true)
        ;

        yield ChoiceField::new('starLevel', '星级')
            ->setColumns(6)
            ->setChoices([
                '一星级' => 1,
                '二星级' => 2,
                '三星级' => 3,
                '四星级' => 4,
                '五星级' => 5,
            ])
            ->renderAsBadges([
                1 => 'warning',
                2 => 'warning',
                3 => 'info',
                4 => 'primary',
                5 => 'success',
            ])
        ;

        yield TextField::new('address', '详细地址')
            ->setColumns(12)
            ->setRequired(true)
        ;

        yield FormField::addFieldset('联系信息')->setIcon('fa fa-address-book');

        yield TextField::new('contactPerson', '联系人')
            ->setColumns(6)
            ->setRequired(true)
        ;

        yield TextField::new('phone', '联系电话')
            ->setColumns(6)
            ->setRequired(true)
        ;

        yield EmailField::new('email', '邮箱')
            ->setColumns(6)
        ;

        yield ChoiceField::new('status', '状态')
            ->setColumns(6)
            ->setChoices([
                '运营中' => HotelStatusEnum::OPERATING,
                '暂停合作' => HotelStatusEnum::SUSPENDED,
            ])
            ->renderAsBadges([
                HotelStatusEnum::OPERATING->value => 'success',
                HotelStatusEnum::SUSPENDED->value => 'danger',
            ])
        ;

        yield FormField::addFieldset('设施与服务')->setIcon('fa fa-concierge-bell');

        yield ArrayField::new('facilities', '设施与服务')
            ->setColumns(12)
            ->setHelp('添加酒店拥有的设施和服务，如：餐厅、游泳池、健身房、接机服务等')
        ;

        yield FormField::addFieldset('房型管理')->setIcon('fa fa-bed')
            ->hideOnForm()
        ;

        yield CollectionField::new('roomTypes', '房型列表')
            ->onlyOnDetail()
            ->setTemplatePath('@HotelProfile/admin/field/room_types.html.twig')
        ;

        yield FormField::addFieldset('系统信息')->setIcon('fa fa-info-circle')
            ->hideOnForm()
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->onlyOnDetail()
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->onlyOnDetail()
        ;
    }

    /**
     * 导出酒店数据到Excel
     *
     * @codeCoverageIgnore AdminContext 是 final 类，无法在单元测试中创建 mock
     */
    #[AdminAction(routePath: 'export', routeName: 'export')]
    public function exportHotels(AdminContext $context): Response
    {
        $exportResult = $this->importExportService->exportHotelsToExcel();

        return $this->file(
            $exportResult['file_path'],
            $exportResult['file_name'],
            $exportResult['disposition']
        );
    }

    /**
     * 显示酒店数据导入表单
     *
     * @codeCoverageIgnore AdminContext 是 final 类，无法在单元测试中创建 mock
     */
    #[AdminAction(routePath: 'import', routeName: 'import')]
    public function importHotelsForm(AdminContext $context, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $this->processImportRequest($request);

            // 导入完成后安全重定向到列表页面，避免EasyAdmin上下文丢失
            return $this->redirectToCrudIndex($context);
        }

        return $this->render('@HotelProfile/admin/import.html.twig');
    }

    private function processImportRequest(Request $request): void
    {
        $excelFile = $request->files->get('excel_file');
        assert($excelFile instanceof UploadedFile || null === $excelFile);

        if (null === $excelFile) {
            $this->addFlash('danger', '请选择Excel文件');

            return;
        }

        $result = $this->importExportService->importHotelsFromExcel($excelFile);
        $this->handleImportResult($result);
    }

    /**
     * @param array{success: bool, import_count: int, errors: array<string>} $result
     */
    private function handleImportResult(array $result): void
    {
        if (!$result['success']) {
            $this->addErrorMessages($result['errors']);

            return;
        }

        if (count($result['errors']) > 0) {
            $this->addFlash('warning', "导入完成，成功导入 {$result['import_count']} 条记录，但有 " . count($result['errors']) . ' 条记录有错误');
            $this->addErrorMessages($result['errors']);
        } else {
            $this->addFlash('success', "成功导入 {$result['import_count']} 条酒店记录");
        }
    }

    /**
     * @param array<string> $errors
     */
    private function addErrorMessages(array $errors): void
    {
        foreach ($errors as $error) {
            $this->addFlash('danger', $error);
        }
    }

    /**
     * 下载酒店数据导入模板
     *
     * @codeCoverageIgnore AdminContext 是 final 类，无法在单元测试中创建 mock
     */
    #[AdminAction(routePath: 'download-template', routeName: 'download-template')]
    public function downloadImportTemplate(AdminContext $context): Response
    {
        $templateResult = $this->importExportService->createImportTemplate();

        return $this->file(
            $templateResult['file_path'],
            $templateResult['file_name'],
            $templateResult['disposition']
        );
    }

    /**
     * 安全地重定向到CRUD列表页面，确保EasyAdmin上下文完整
     */
    private function redirectToCrudIndex(?AdminContext $context = null): Response
    {
        // 优先使用referer保留完整上下文
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $referer = $request->headers->get('referer');
            if ($referer && !str_contains($referer, 'importHotelsForm') && !str_contains($referer, 'downloadImportTemplate')) {
                return $this->redirect($referer);
            }
        }

        // 如果没有有效的referer，使用标准的EasyAdmin重定向方式
        if ($context) {
            return $this->redirectToRoute('admin', [
                'crudAction' => 'index',
                'crudControllerFqcn' => static::class,
            ]);
        }

        // 最后的备用方案
        return $this->redirectToRoute('admin');
    }
}
