<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Role;

use Klipper\Component\Security\Model\RoleInterface;

/**
 * Utils for role.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class RoleUtil
{
    /**
     * Format the role names.
     *
     * @param RoleInterface[]|string[] $roles The roles
     *
     * @return string[]
     */
    public static function formatNames(array $roles): array
    {
        return array_map(static function ($role) {
            return static::formatName($role);
        }, $roles);
    }

    /**
     * Format the role name.
     *
     * @param RoleInterface|string $role The role
     */
    public static function formatName($role): string
    {
        return $role instanceof RoleInterface ? $role->getName() : (string) $role;
    }
}
