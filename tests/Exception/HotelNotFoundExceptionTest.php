<?php

namespace Tourze\HotelProfileBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\HotelProfileBundle\Exception\HotelNotFoundException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(HotelNotFoundException::class)]
final class HotelNotFoundExceptionTest extends AbstractExceptionTestCase
{
    public function testConstructor(): void
    {
        $hotelId = 123;
        $exception = new HotelNotFoundException($hotelId);

        self::assertSame('Hotel with ID 123 not found', $exception->getMessage());
        self::assertSame($hotelId, $exception->getCode());
    }
}
