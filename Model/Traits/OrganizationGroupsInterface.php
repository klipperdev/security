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
use Klipper\Component\Security\Model\GroupInterface;
use Klipper\Component\Security\Model\OrganizationInterface;

/**
 * Trait of groups in organization model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface OrganizationGroupsInterface extends OrganizationInterface
{
    /**
     * Get the groups of organization.
     *
     * @return Collection|GroupInterface[]
     */
    public function getOrganizationGroups();

    /**
     * Get the group names of organization.
     *
     * @return string[]
     */
    public function getOrganizationGroupNames(): array;

    /**
     * Check the presence of group in organization.
     *
     * @param string $group The group name
     */
    public function hasOrganizationGroup(string $group): bool;

    /**
     * Add a group in organization.
     *
     * @param GroupInterface $group The group
     *
     * @return static
     */
    public function addOrganizationGroup(GroupInterface $group);

    /**
     * Remove a group in organization.
     *
     * @param GroupInterface $group The group
     *
     * @return static
     */
    public function removeOrganizationGroup(GroupInterface $group);
}
