<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Permission;

use Klipper\Component\Security\CacheWarmer\AbstractCacheWarmer;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PermissionFactoryCacheWarmer extends AbstractCacheWarmer
{
    public static function getSubscribedServices(): array
    {
        return [
            'klipper_security.permission_factory' => PermissionFactoryInterface::class,
        ];
    }
}
