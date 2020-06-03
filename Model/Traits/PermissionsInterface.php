<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Model\Traits;

use Doctrine\Common\Collections\Collection;
use Klipper\Component\Security\Model\PermissionInterface;

/**
 * Interface of model with permissions.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface PermissionsInterface
{
    /**
     * Get the permissions.
     *
     * @return Collection|PermissionInterface[]
     */
    public function getPermissions(): Collection;

    /**
     * Check if the role has the permission.
     *
     * @param PermissionInterface $permission The permission
     */
    public function hasPermission(PermissionInterface $permission): bool;

    /**
     * Add the permission.
     *
     * @param PermissionInterface $permission The permission
     *
     * @return static
     */
    public function addPermission(PermissionInterface $permission);

    /**
     * Remove the permission.
     *
     * @param PermissionInterface $permission The permission
     *
     * @return static
     */
    public function removePermission(PermissionInterface $permission);
}
