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
 * Interface for organization hierarchical.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface OrganizationHierarchicalInterface extends OrganizationInterface
{
    /**
     * Set the parent organization on the current organization.
     *
     * @return static
     */
    public function setParent(?OrganizationHierarchicalInterface $parent);

    /**
     * Get the parent organization.
     *
     * @return OrganizationHierarchicalInterface
     */
    public function getParent(): ?OrganizationHierarchicalInterface;

    /**
     * Get all organization children.
     *
     * @return Collection|OrganizationHierarchicalInterface[]
     */
    public function getChildren(): Collection;
}
