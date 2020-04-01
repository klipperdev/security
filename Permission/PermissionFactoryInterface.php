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

/**
 * Permission factory interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface PermissionFactoryInterface
{
    /**
     * Create the permission configurations.
     */
    public function createConfigurations(): PermissionConfigCollection;
}
