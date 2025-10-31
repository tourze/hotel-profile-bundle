# hotel-profile-bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/hotel-profile-bundle.svg?style=flat-square)]
(https://packagist.org/packages/tourze/hotel-profile-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/hotel-profile-bundle.svg?style=flat-square)]
(https://packagist.org/packages/tourze/hotel-profile-bundle)
[![Symfony Version](https://img.shields.io/badge/symfony-%5E7.3-brightgreen.svg?style=flat-square)](https://symfony.com)
[![License](https://img.shields.io/packagist/l/tourze/hotel-profile-bundle.svg?style=flat-square)](LICENSE)


Hotel profile management bundle for Symfony applications. This bundle provides comprehensive hotel and room type management functionality with administrative interfaces, Excel import/export capabilities, and EasyAdmin integration.

> **Note**: This is an internal package for the monorepo architecture and is not published to Packagist.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Requirements](#requirements)
- [Configuration](#configuration)
- [Entities](#entities)
- [Services](#services)
- [Enums](#enums)
- [Admin Interface](#admin-interface)
- [API Endpoints](#api-endpoints)
- [Advanced Usage](#advanced-usage)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)
- [Support](#support)

## Features

- **Hotel Management**: Complete CRUD operations for hotel profiles
- **Room Type Management**: Room type configurations for each hotel
- **Excel Import/Export**: Bulk import and export hotel data via Excel files
- **EasyAdmin Integration**: Ready-to-use administrative interface
- **Status Management**: Hotel operational status tracking
- **Validation**: Comprehensive data validation with Symfony constraints
- **Audit Trail**: Automatic timestamp tracking for all entities

## Installation

### Step 1: Monorepo Usage

This bundle is designed for use within the monorepo architecture. It's automatically available when you set up the monorepo project.

### Step 2: Enable the Bundle

The bundle is automatically registered in the Symfony kernel. You can verify it's enabled by checking:

```bash
bin/console debug:container --tag=kernel.bundles
```

### Step 3: Update Database Schema

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### Step 4: Verify Installation

```bash
# Check if hotel-related routes are available
bin/console debug:router | grep hotel

# Check if services are registered
bin/console debug:container HotelService
```

## Quick Start

### Basic Usage

```php
<?php

use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;
use Tourze\HotelProfileBundle\Service\HotelService;

// Create a new hotel
$hotel = new Hotel();
$hotel->setName('Grand Hotel');
$hotel->setAddress('123 Main Street');
$hotel->setStarLevel(5);
$hotel->setContactPerson('John Doe');
$hotel->setPhone('+1234567890');
$hotel->setStatus(HotelStatusEnum::OPERATING);

// Use the hotel service
$hotelService = $container->get(HotelService::class);

// Find hotels by status
$operatingHotels = $hotelService->findHotelsByStatus(HotelStatusEnum::OPERATING);

// Update hotel status
$hotelService->updateHotelStatus($hotelId, HotelStatusEnum::SUSPENDED);
```

## Requirements

- PHP 8.1+
- Symfony 7.3+
- Doctrine ORM 3.0+
- EasyAdmin Bundle 4+
- Doctrine DBAL 4.0+
- PHPSpreadsheet for Excel operations

## Configuration

The bundle requires minimal configuration. After installation, it automatically registers all necessary services.

## Entities

### Hotel Entity

The `Hotel` entity includes:
- Basic information (name, address, contact details)
- Star rating (1-5 stars)
- Photo gallery (JSON array of URLs)
- Facilities information (JSON array)
- Operational status
- One-to-many relationship with room types

### Room Type Entity

The `RoomType` entity includes:
- Room type details and pricing
- Availability status
- Association with hotel

## Services

### HotelService

Core business service for hotel operations:

```php
use Tourze\HotelProfileBundle\Service\HotelService;

// Find hotels by status
$operatingHotels = $hotelService->findHotelsByStatus(HotelStatusEnum::OPERATING);

// Update hotel status
$hotelService->updateHotelStatus($hotelId, HotelStatusEnum::SUSPENDED);
```

### RoomTypeService

Room type management service:

```php
use Tourze\HotelProfileBundle\Service\RoomTypeService;

// Find room types by hotel
$roomTypes = $roomTypeService->findRoomTypesByHotel($hotelId);

// Find room types by status
$activeRoomTypes = $roomTypeService->findRoomTypesByStatus(RoomTypeStatusEnum::ACTIVE);
```

### HotelImportExportService

Excel import/export functionality:

```php
use Tourze\HotelProfileBundle\Service\HotelImportExportService;

// Export hotels to Excel
$exportResult = $importExportService->exportHotelsToExcel();

// Create import template
$template = $importExportService->createImportTemplate();

// Import hotels from Excel
$importResult = $importExportService->importHotelsFromExcel($uploadedFile);
```

## Enums

### HotelStatusEnum

Hotel operational status:
- `OPERATING`: Hotel is currently operating
- `SUSPENDED`: Hotel cooperation is suspended

### RoomTypeStatusEnum

Room type availability status:
- `ACTIVE`: Room type is available for booking
- `DISABLED`: Room type is temporarily disabled

## Admin Interface

The bundle provides EasyAdmin CRUD controllers:
- Hotel management at `/admin/hotel`
- Room type management at `/admin/room-type`
- Import/export functionality in admin interface

## API Endpoints

Administrative API endpoints are available for:
- Room type management
- Hotel data operations

## Advanced Usage

### Custom Validation Rules

You can extend the bundle's validation by creating custom validators:

```php
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CustomHotelValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        // Custom validation logic
    }
}
```

### Event Listeners

Listen to hotel-related events:

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
        // Handle hotel creation
    }
}
```

### Custom Export Formats

Extend the import/export service to support additional formats:

```php
use Tourze\HotelProfileBundle\Service\HotelImportExportService;

class CustomImportExportService extends HotelImportExportService
{
    public function exportToCsv(): Response
    {
        // Custom CSV export logic
    }
}
```

### Data Fixtures

Sample data fixtures are provided for testing:
- `HotelFixtures`: Sample hotel data
- `RoomTypeFixtures`: Sample room type configurations

## Testing

The bundle includes comprehensive test coverage:

```bash
# Run all tests
vendor/bin/phpunit packages/hotel-profile-bundle/tests

# Run specific test categories
vendor/bin/phpunit packages/hotel-profile-bundle/tests/Entity
vendor/bin/phpunit packages/hotel-profile-bundle/tests/Service
vendor/bin/phpunit packages/hotel-profile-bundle/tests/Controller

# Run with coverage
vendor/bin/phpunit packages/hotel-profile-bundle/tests --coverage-html coverage/

# Run static analysis
vendor/bin/phpstan analyse packages/hotel-profile-bundle
```

## Contributing

We welcome contributions to this bundle! Here's how you can help:

### Reporting Issues

If you find a bug or have a feature request, please create an issue on our GitHub repository.

### Submitting Pull Requests

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run tests: `vendor/bin/phpunit packages/hotel-profile-bundle/tests`
5. Run static analysis: `vendor/bin/phpstan analyse packages/hotel-profile-bundle`
6. Commit your changes (`git commit -m 'Add amazing feature'`)
7. Push to the branch (`git push origin feature/amazing-feature`)
8. Open a Pull Request

### Code Standards

- Follow PSR-12 coding standards
- Add PHPDoc comments for all public methods
- Write unit tests for new features
- Ensure backward compatibility

## License

This bundle is open-sourced software licensed under the MIT License. Please see the 
[LICENSE](LICENSE) file for more information.

## Support

For issues and feature requests, please use the GitHub issue tracker.