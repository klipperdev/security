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
use Klipper\Component\Security\Model\OrganizationUserInterface;

/**
 * Trait of organization users in user model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait UserOrganizationUsersTrait
{
    /**
     * @var null|Collection|OrganizationUserInterface[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Klipper\Component\Security\Model\OrganizationUserInterface",
     *     mappedBy="user",
     *     fetch="EXTRA_LAZY",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"}
     * )
     * @ORM\OrderBy({"organization": "ASC"})
     */
    protected ?Collection $userOrganizations = null;

    /**
     * {@inheritdoc}
     */
    public function getUserOrganizations(): Collection
    {
        return $this->userOrganizations ?: $this->userOrganizations = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserOrganizationNames(): array
    {
        $names = [];
        foreach ($this->getUserOrganizations() as $userOrg) {
            $names[] = $userOrg->getOrganization()->getName();
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     */
    public function hasUserOrganization(string $name): bool
    {
        return \in_array($name, $this->getUserOrganizationNames(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserOrganization(string $name): ?OrganizationUserInterface
    {
        $org = null;

        foreach ($this->getUserOrganizations() as $userOrg) {
            if ($name === $userOrg->getOrganization()->getName()) {
                $org = $userOrg;

                break;
            }
        }

        return $org;
    }

    /**
     * {@inheritdoc}
     */
    public function addUserOrganization(OrganizationUserInterface $organizationUser): self
    {
        $org = $organizationUser->getOrganization();

        if ($org && !$org->isUserOrganization()
            && !$this->getUserOrganizations()->contains($organizationUser)) {
            $this->getUserOrganizations()->add($organizationUser);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeUserOrganization(OrganizationUserInterface $organizationUser): self
    {
        if ($this->getUserOrganizations()->contains($organizationUser)) {
            $this->getUserOrganizations()->removeElement($organizationUser);
        }

        return $this;
    }
}
