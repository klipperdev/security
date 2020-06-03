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
use Klipper\Component\Security\Model\RoleHierarchicalInterface;
use Klipper\Component\Security\Model\RoleInterface;

/**
 * Trait of hierarchical for role model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait RoleHierarchicalTrait
{
    /**
     * @var null|Collection|RoleInterface[]
     *
     * @ORM\ManyToMany(
     *     targetEntity="Klipper\Component\Security\Model\RoleInterface",
     *     mappedBy="children"
     * )
     */
    protected ?Collection $parents = null;

    /**
     * @var null|Collection|RoleInterface[]
     *
     * @ORM\ManyToMany(
     *     targetEntity="Klipper\Component\Security\Model\RoleInterface",
     *     inversedBy="parents"
     * )
     * @ORM\JoinTable(
     *     name="role_children",
     *     joinColumns={
     *         @ORM\JoinColumn(onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(onDelete="CASCADE", name="children_role_id")
     *     }
     * )
     */
    protected ?Collection $children = null;

    public function addParent(RoleHierarchicalInterface $role): self
    {
        /** @var RoleHierarchicalInterface $self */
        $self = $this;
        $role->addChild($self);
        $this->getParents()->add($role);

        return $this;
    }

    public function removeParent(RoleHierarchicalInterface $parent): self
    {
        if ($this->getParents()->contains($parent)) {
            $this->getParents()->removeElement($parent);
            $parent->getChildren()->removeElement($this);
        }

        return $this;
    }

    public function getParents(): Collection
    {
        return $this->parents ?: $this->parents = new ArrayCollection();
    }

    public function getParentNames(): array
    {
        $names = [];

        /** @var RoleInterface $parent */
        foreach ($this->getParents() as $parent) {
            $names[] = $parent->getName();
        }

        return $names;
    }

    public function hasParent(string $name): bool
    {
        return \in_array($name, $this->getParentNames(), true);
    }

    public function addChild(RoleHierarchicalInterface $role): self
    {
        $this->getChildren()->add($role);

        return $this;
    }

    public function removeChild(RoleHierarchicalInterface $child): self
    {
        if ($this->getChildren()->contains($child)) {
            $this->getChildren()->removeElement($child);
        }

        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children ?: $this->children = new ArrayCollection();
    }

    public function getChildrenNames(): array
    {
        $names = [];

        /** @var RoleInterface $child */
        foreach ($this->getChildren() as $child) {
            $names[] = $child->getName();
        }

        return $names;
    }

    public function hasChild(string $name): bool
    {
        return \in_array($name, $this->getChildrenNames(), true);
    }
}
