<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\CacheWarmer;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractCacheWarmer implements CacheWarmerInterface, ServiceSubscriberInterface
{
    private ContainerInterface $container;

    private ?object $cacheService = null;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param mixed $cacheDir
     */
    public function warmUp($cacheDir): void
    {
        if (null === $this->cacheService) {
            $this->cacheService = $this->container->get(array_keys(static::getSubscribedServices())[0]);
        }

        if ($this->cacheService instanceof WarmableInterface) {
            $this->cacheService->warmUp($cacheDir);
        }
    }

    public function isOptional(): bool
    {
        return true;
    }
}
