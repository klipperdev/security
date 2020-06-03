<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Event;

use Klipper\Component\Security\Identity\SecurityIdentityInterface;

/**
 * The post load permissions event.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PostLoadPermissionsEvent extends AbstractLoadPermissionsEvent
{
    protected array $permissionMap;

    /**
     * @param SecurityIdentityInterface[] $sids          The security identities
     * @param string[]                    $roles         The role names
     * @param array                       $permissionMap The map of permissions
     */
    public function __construct(array $sids, array $roles, array $permissionMap)
    {
        parent::__construct($sids, $roles);

        $this->permissionMap = $permissionMap;
    }

    /**
     * Set the map of permissions.
     *
     * @param array $permissionMap The map of permissions
     */
    public function setPermissionMap(array $permissionMap): void
    {
        $this->permissionMap = $permissionMap;
    }

    /**
     * Get the map of permissions.
     */
    public function getPermissionMap(): array
    {
        return $this->permissionMap;
    }
}
