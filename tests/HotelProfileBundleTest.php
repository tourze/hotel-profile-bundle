<?php

declare(strict_types=1);

namespace Tourze\HotelProfileBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\HotelProfileBundle\HotelProfileBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(HotelProfileBundle::class)]
#[RunTestsInSeparateProcesses]
final class HotelProfileBundleTest extends AbstractBundleTestCase
{
}
