<?php

namespace Tourze\HotelProfileBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle;
use Tourze\EasyAdminMenuBundle\EasyAdminMenuBundle;
use Tourze\RoutingAutoLoaderBundle\RoutingAutoLoaderBundle;

class HotelProfileBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            EasyAdminMenuBundle::class => ['all' => true],
            DoctrineBundle::class => ['all' => true],
            TwigBundle::class => ['all' => true],
            DoctrineTimestampBundle::class => ['all' => true],
            RoutingAutoLoaderBundle::class => ['all' => true],
        ];
    }
}
