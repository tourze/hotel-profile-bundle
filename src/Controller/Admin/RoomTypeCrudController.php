<?php

namespace Tourze\HotelProfileBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\HttpFoundation\RequestStack;
use Tourze\HotelProfileBundle\Entity\RoomType;
use Tourze\HotelProfileBundle\Enum\RoomTypeStatusEnum;

#[AdminCrud(routePath: '/hotel-profile/room-type', routeName: 'hotel_profile_room_type')]
final class RoomTypeCrudController extends AbstractCrudController
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public static function getEntityFqcn(): string
    {
        return RoomType::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('房型')
            ->setEntityLabelInPlural('房型列表')
            ->setPageTitle('index', '房型管理')
            ->setPageTitle('new', '添加房型')
            ->setPageTitle('edit', fn (RoomType $roomType) => sprintf('编辑房型: %s', $roomType->getName()))
            ->setPageTitle('detail', fn (RoomType $roomType) => sprintf('房型详情: %s', $roomType->getName()))
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['name', 'bedType', 'hotel.name', 'code'])
            ->showEntityActionsInlined()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('hotel', '所属酒店'))
            ->add(TextFilter::new('name', '房型名称'))
            ->add(TextFilter::new('code', '房型代码'))
            ->add(ChoiceFilter::new('status', '状态')->setChoices([
                '可用' => RoomTypeStatusEnum::ACTIVE->value,
                '停用' => RoomTypeStatusEnum::DISABLED->value,
            ]))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $request = $this->requestStack->getCurrentRequest();

        yield FormField::addFieldset('基本信息')->setIcon('fa fa-bed');

        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield AssociationField::new('hotel', '所属酒店')
            ->setColumns(12)
            ->setRequired(true)
        ;

        yield TextField::new('name', '房型名称')
            ->setColumns(6)
            ->setRequired(true)
        ;

        yield TextField::new('code', '房型代码')
            ->setColumns(6)
            ->setHelp('用于生成库存标识符，如DBL（双床）、KNG（大床）等')
        ;

        yield ChoiceField::new('status', '状态')
            ->setColumns(6)
            ->setChoices([
                '可用' => RoomTypeStatusEnum::ACTIVE,
                '停用' => RoomTypeStatusEnum::DISABLED,
            ])
            ->renderAsBadges([
                RoomTypeStatusEnum::ACTIVE->value => 'success',
                RoomTypeStatusEnum::DISABLED->value => 'danger',
            ])
        ;

        yield NumberField::new('area', '面积(平方米)')
            ->setColumns(4)
            ->setNumDecimals(1)
            ->setRequired(true)
        ;

        yield TextField::new('bedType', '床型')
            ->setColumns(4)
            ->setRequired(true)
            ->setHelp('如：大床、双床、三床等')
        ;

        yield IntegerField::new('maxGuests', '最大入住人数')
            ->setColumns(4)
            ->setRequired(true)
        ;

        yield IntegerField::new('breakfastCount', '含早份数')
            ->setColumns(4)
            ->setRequired(true)
        ;

        yield FormField::addFieldset('详情描述')->setIcon('fa fa-align-left');

        yield TextareaField::new('description', '房型描述')
            ->setColumns(12)
            ->hideOnIndex()
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
}
