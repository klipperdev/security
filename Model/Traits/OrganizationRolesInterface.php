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
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\RoleInterface;

/**
 * Trait of roles in organization model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface OrganizationRolesInterface extends OrganizationInterface
{
    /**
     * Get the roles of organization.
     *
     * @return Collection|RoleInterface[]
     */
    public function getOrganizationRoles();

    /**
     * Get the role names of organization.
     *
     * @return string[]
     */
    public function getOrganizationRoleNames(): array;

    /**
     * Check the presence of role in organization.
     *
     * @param string $role The role name
     *
     * @return bool
     */
    public function hasOrganizationRole(string $role): bool;

    /**
     * Add a role in organization.
     *
     * @param RoleInterface $role The role
     *
     * @return static
     */
    public function addOrganizationRole(RoleInterface $role);

    /**
     * Remove a role in organization.
     *
     * @param RoleInterface $role The role
     *
     * @return static
     */
    public function removeOrganizationRole(RoleInterface $role);
}
