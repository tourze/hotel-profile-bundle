<?php

namespace Tourze\HotelProfileBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\HotelProfileBundle\DependencyInjection\HotelProfileExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(HotelProfileExtension::class)]
final class HotelProfileExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
}
