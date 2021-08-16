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
use Klipper\Component\Security\Model\RoleInterface;

/**
 * Trait of roles in organization model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait OrganizationRolesTrait
{
    /**
     * @var null|Collection|RoleInterface[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Klipper\Component\Security\Model\RoleInterface",
     *     fetch="EXTRA_LAZY",
     *     mappedBy="organization",
     *     cascade={"persist", "remove"}
     * )
     */
    protected ?Collection $organizationRoles = null;

    public function getOrganizationRoles(): Collection
    {
        return $this->organizationRoles ?: $this->organizationRoles = new ArrayCollection();
    }

    public function getOrganizationRoleNames(): array
    {
        $names = [];
        foreach ($this->getOrganizationRoles() as $role) {
            $names[] = $role->getName();
        }

        return $names;
    }

    public function hasOrganizationRole(string $role): bool
    {
        return \in_array($role, $this->getOrganizationRoleNames(), true);
    }

    public function addOrganizationRole(RoleInterface $role): self
    {
        if (!$this->isUserOrganization()
            && !$this->getOrganizationRoles()->contains($role)) {
            $this->getOrganizationRoles()->add($role);
        }

        return $this;
    }

    public function removeOrganizationRole(RoleInterface $role): self
    {
        if ($this->getOrganizationRoles()->contains($role)) {
            $this->getOrganizationRoles()->removeElement($role);
        }

        return $this;
    }

    /**
     * Check if the organization is a user organization or not.
     */
    abstract public function isUserOrganization(): bool;
}
