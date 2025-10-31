<?php

namespace Tourze\HotelProfileBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class HotelProfileExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
