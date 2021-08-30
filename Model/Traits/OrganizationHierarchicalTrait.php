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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Klipper\Component\Security\Model\OrganizationHierarchicalInterface;

/**
 * Trait of hierarchical for organization model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait OrganizationHierarchicalTrait
{
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Klipper\Component\Security\Model\OrganizationHierarchicalInterface",
     *     fetch="EXTRA_LAZY",
     *     inversedBy="children"
     * )
     */
    protected ?OrganizationHierarchicalInterface $parent = null;

    /**
     * @var null|Collection|OrganizationHierarchicalInterface[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Klipper\Component\Security\Model\OrganizationHierarchicalInterface",
     *     mappedBy="parent"
     * )
     */
    private ?Collection $children = null;

    /**
     * @see OrganizationHierarchicalInterface::setParent()
     */
    public function setParent(?OrganizationHierarchicalInterface $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @see OrganizationHierarchicalInterface::getParent()
     */
    public function getParent(): ?OrganizationHierarchicalInterface
    {
        return $this->parent;
    }

    /**
     * @see OrganizationHierarchicalInterface::getChildren()
     */
    public function getChildren(): Collection
    {
        return $this->children ?: $this->children = new ArrayCollection();
    }
}
