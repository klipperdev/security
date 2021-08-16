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
use Klipper\Component\Security\Model\GroupInterface;

/**
 * Trait of groups in organization model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait OrganizationGroupsTrait
{
    /**
     * @var null|Collection|GroupInterface[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Klipper\Component\Security\Model\GroupInterface",
     *     fetch="EXTRA_LAZY",
     *     mappedBy="organization",
     *     cascade={"persist", "remove"}
     * )
     */
    protected ?Collection $organizationGroups = null;

    public function getOrganizationGroups(): Collection
    {
        return $this->organizationGroups ?: $this->organizationGroups = new ArrayCollection();
    }

    public function getOrganizationGroupNames(): array
    {
        $names = [];
        foreach ($this->getOrganizationGroups() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    public function hasOrganizationGroup(string $group): bool
    {
        return \in_array($group, $this->getOrganizationGroupNames(), true);
    }

    public function addOrganizationGroup(GroupInterface $group): self
    {
        if (!$this->isUserOrganization()
            && !$this->getOrganizationGroups()->contains($group)) {
            $this->getOrganizationGroups()->add($group);
        }

        return $this;
    }

    public function removeOrganizationGroup(GroupInterface $group): self
    {
        if ($this->getOrganizationGroups()->contains($group)) {
            $this->getOrganizationGroups()->removeElement($group);
        }

        return $this;
    }

    /**
     * Check if the organization is a user organization or not.
     */
    abstract public function isUserOrganization(): bool;
}
