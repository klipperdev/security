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
 * Permission vote.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PermVote
{
    private string $permission;

    /**
     * @param string $permission The permission name
     */
    public function __construct(string $permission)
    {
        $this->permission = $permission;
    }

    /**
     * Get the field name.
     */
    public function getPermission(): string
    {
        return $this->permission;
    }
}
