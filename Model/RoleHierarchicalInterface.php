<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Model;

use Doctrine\Common\Collections\Collection;

/**
 * Interface for role hierarchical.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface RoleHierarchicalInterface extends RoleInterface
{
    /**
     * Add a parent on the current role.
     *
     * @return static
     */
    public function addParent(RoleHierarchicalInterface $role);

    /**
     * Remove a parent on the current role.
     *
     * @return static
     */
    public function removeParent(RoleHierarchicalInterface $parent);

    /**
     * Gets all parent.
     *
     * @return Collection|RoleHierarchicalInterface[]
     */
    public function getParents();

    /**
     * Gets all parent names.
     */
    public function getParentNames(): array;

    /**
     * Check if role has parent.
     */
    public function hasParent(string $name): bool;

    /**
     * Add a child on the current role.
     *
     * @return static
     */
    public function addChild(RoleHierarchicalInterface $role);

    /**
     * Remove a child on the current role.
     *
     * @return static
     */
    public function removeChild(RoleHierarchicalInterface $child);

    /**
     * Gets all children.
     *
     * @return Collection|RoleHierarchicalInterface[]
     */
    public function getChildren();

    /**
     * Gets all children names.
     *
     * @return string[]
     */
    public function getChildrenNames(): array;

    /**
     * Check if role has child.
     */
    public function hasChild(string $name): bool;
}
