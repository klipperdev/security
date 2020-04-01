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

/**
 * Interface of roleable model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface RoleableInterface
{
    /**
     * Check if the role exist.
     *
     * @param string $role The role name
     *
     * @return bool
     */
    public function hasRole(string $role): bool;

    /**
     * Set the roles.
     *
     * This overwrites any previous roles.
     *
     * @param string[] $roles The roles
     *
     * @return static
     */
    public function setRoles(array $roles);

    /**
     * Add a role.
     *
     * @param string $role The role name
     *
     * @return static
     */
    public function addRole(string $role);

    /**
     * Remove a role.
     *
     * @param string $role The role name
     *
     * @return static
     */
    public function removeRole(string $role);

    /**
     * Get the roles.
     *
     * @return string[] The user roles
     */
    public function getRoles();
}
