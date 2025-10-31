# hotel-profile-bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/hotel-profile-bundle.svg?style=flat-square)]
(https://packagist.org/packages/tourze/hotel-profile-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/hotel-profile-bundle.svg?style=flat-square)]
(https://packagist.org/packages/tourze/hotel-profile-bundle)
[![Symfony Version](https://img.shields.io/badge/symfony-%5E7.3-brightgreen.svg?style=flat-square)](https://symfony.com)
[![License](https://img.shields.io/packagist/l/tourze/hotel-profile-bundle.svg?style=flat-square)](LICENSE)


Symfony 应用程序的酒店档案管理模块。此模块提供完整的酒店和房型管理功能，包含管理后台界面、Excel 导入/导出功能和 EasyAdmin 集成。

> **注意**: 这是 monorepo 架构的内部包，不会发布到 Packagist。

## 目录

- [功能特性](#功能特性)
- [安装](#安装)
- [快速开始](#快速开始)
- [系统要求](#系统要求)
- [配置](#配置)
- [实体](#实体)
- [服务](#服务)
- [枚举](#枚举)
- [管理界面](#管理界面)
- [API 接口](#api-接口)
- [高级用法](#高级用法)
- [测试](#测试)
- [贡献](#贡献)
- [许可证](#许可证)
- [支持](#支持)

## 功能特性

- **酒店管理**: 完整的酒店档案 CRUD 操作
- **房型管理**: 每个酒店的房型配置管理
- **Excel 导入/导出**: 通过 Excel 文件批量导入和导出酒店数据
- **EasyAdmin 集成**: 开箱即用的管理后台界面
- **状态管理**: 酒店运营状态跟踪
- **数据验证**: 使用 Symfony 约束进行全面的数据验证
- **审计跟踪**: 所有实体的自动时间戳记录

## 安装

### 步骤 1: Monorepo 使用

此模块专为 monorepo 架构而设计。当您设置 monorepo 项目时，它会自动可用。

### 步骤 2: 启用模块

模块会在 Symfony 内核中自动注册。您可以通过以下命令验证它是否启用：

```bash
bin/console debug:container --tag=kernel.bundles
```

### 步骤 3: 更新数据库架构

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### 步骤 4: 验证安装

```bash
# 检查酒店相关路由是否可用
bin/console debug:router | grep hotel

# 检查服务是否注册
bin/console debug:container HotelService
```

## 快速开始

### 基本使用

```php
<?php

use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;
use Tourze\HotelProfileBundle\Service\HotelService;

// 创建新酒店
$hotel = new Hotel();
$hotel->setName('豪华大酒店');
$hotel->setAddress('北京市朝阳区建国门外大街1号');
$hotel->setStarLevel(5);
$hotel->setContactPerson('张三');
$hotel->setPhone('+86-10-12345678');
$hotel->setStatus(HotelStatusEnum::OPERATING);

// 使用酒店服务
$hotelService = $container->get(HotelService::class);

// 按状态查找酒店
$operatingHotels = $hotelService->findHotelsByStatus(HotelStatusEnum::OPERATING);

// 更新酒店状态
$hotelService->updateHotelStatus($hotelId, HotelStatusEnum::SUSPENDED);
```

## 系统要求

- PHP 8.1+
- Symfony 7.3+
- Doctrine ORM 3.0+
- EasyAdmin Bundle 4+
- Doctrine DBAL 4.0+
- PHPSpreadsheet（用于 Excel 操作）

## 配置

模块只需要最少的配置。安装后，它会自动注册所有必要的服务。

## 实体

### Hotel 实体

`Hotel` 实体包含：
- 基本信息（名称、地址、联系详情）
- 星级评定（1-5 星）
- 照片图库（JSON 数组格式的 URL）
- 设施信息（JSON 数组）
- 运营状态
- 与房型的一对多关系

### RoomType 实体

`RoomType` 实体包含：
- 房型详情和定价
- 可用状态
- 与酒店的关联

## 服务

### HotelService

酒店操作的核心业务服务：

```php
use Tourze\HotelProfileBundle\Service\HotelService;

// 按状态查找酒店
$operatingHotels = $hotelService->findHotelsByStatus(HotelStatusEnum::OPERATING);

// 更新酒店状态
$hotelService->updateHotelStatus($hotelId, HotelStatusEnum::SUSPENDED);
```

### RoomTypeService

房型管理服务：

```php
use Tourze\HotelProfileBundle\Service\RoomTypeService;

// 按酒店查找房型
$roomTypes = $roomTypeService->findRoomTypesByHotel($hotelId);

// 按状态查找房型
$activeRoomTypes = $roomTypeService->findRoomTypesByStatus(RoomTypeStatusEnum::ACTIVE);
```

### HotelImportExportService

Excel 导入/导出功能：

```php
use Tourze\HotelProfileBundle\Service\HotelImportExportService;

// 导出酒店到 Excel
$exportResult = $importExportService->exportHotelsToExcel();

// 创建导入模板
$template = $importExportService->createImportTemplate();

// 从 Excel 导入酒店
$importResult = $importExportService->importHotelsFromExcel($uploadedFile);
```

## 枚举

### HotelStatusEnum

酒店运营状态：
- `OPERATING`: 酒店正常运营中
- `SUSPENDED`: 酒店合作暂停

### RoomTypeStatusEnum

房型可用状态：
- `ACTIVE`: 房型可预订
- `DISABLED`: 房型暂时停用

## 管理界面

模块提供 EasyAdmin CRUD 控制器：
- 酒店管理：`/admin/hotel`
- 房型管理：`/admin/room-type`
- 管理界面中的导入/导出功能

## API 接口

提供以下管理 API 接口：
- 房型管理
- 酒店数据操作

## 高级用法

### 自定义验证规则

您可以通过创建自定义验证器来扩展模块的验证：

```php
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CustomHotelValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        // 自定义验证逻辑
    }
}
```

### 事件监听器

监听酒店相关事件：

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HotelEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'hotel.created' => 'onHotelCreated',
            'hotel.updated' => 'onHotelUpdated',
        ];
    }

    public function onHotelCreated(HotelEvent $event): void
    {
        // 处理酒店创建事件
    }
}
```

### 自定义导出格式

扩展导入/导出服务以支持额外的格式：

```php
use Tourze\HotelProfileBundle\Service\HotelImportExportService;

class CustomImportExportService extends HotelImportExportService
{
    public function exportToCsv(): Response
    {
        // 自定义 CSV 导出逻辑
    }
}
```

### 数据装置

提供用于测试的示例数据装置：
- `HotelFixtures`: 示例酒店数据
- `RoomTypeFixtures`: 示例房型配置

## 测试

模块包含全面的测试覆盖：

```bash
# 运行所有测试
vendor/bin/phpunit packages/hotel-profile-bundle/tests

# 运行特定测试分类
vendor/bin/phpunit packages/hotel-profile-bundle/tests/Entity
vendor/bin/phpunit packages/hotel-profile-bundle/tests/Service
vendor/bin/phpunit packages/hotel-profile-bundle/tests/Controller

# 运行带覆盖率报告的测试
vendor/bin/phpunit packages/hotel-profile-bundle/tests --coverage-html coverage/

# 运行静态分析
vendor/bin/phpstan analyse packages/hotel-profile-bundle
```

## 贡献

我们欢迎对此模块的贡献！以下是您可以帮助的方式：

### 报告问题

如果您发现错误或有功能请求，请在我们的 GitHub 仓库中创建问题。

### 提交拉取请求

1. Fork 仓库
2. 创建功能分支 (`git checkout -b feature/amazing-feature`)
3. 进行更改
4. 运行测试：`vendor/bin/phpunit packages/hotel-profile-bundle/tests`
5. 运行静态分析：`vendor/bin/phpstan analyse packages/hotel-profile-bundle`
6. 提交更改 (`git commit -m 'Add amazing feature'`)
7. 推送到分支 (`git push origin feature/amazing-feature`)
8. 打开拉取请求

### 代码标准

- 遵循 PSR-12 编码标准
- 为所有公共方法添加 PHPDoc 注释
- 为新功能编写单元测试
- 确保向后兼容性

## 许可证

此模块是在 MIT 许可证下开源的软件。更多信息请参见 [LICENSE](LICENSE) 文件。

## 支持

如有问题和功能请求，请使用 GitHub 问题跟踪器。