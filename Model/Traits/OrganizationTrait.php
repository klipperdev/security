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
use Klipper\Component\Security\Model\UserInterface;

/**
 * Trait for organization model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait OrganizationTrait
{
    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected ?string $name = null;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Klipper\Component\Security\Model\UserInterface",
     *     inversedBy="organization",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected ?UserInterface $user = null;

    /**
     * @var null|Collection|OrganizationUserInterface[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Klipper\Component\Security\Model\OrganizationUserInterface",
     *     fetch="EXTRA_LAZY",
     *     mappedBy="organization",
     *     cascade={"persist", "remove"}
     * )
     */
    protected ?Collection $organizationUsers = null;

    public function __toString(): string
    {
        return (string) $this->getName();
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function isUserOrganization(): bool
    {
        return null !== $this->getUser();
    }

    public function getOrganizationUsers(): Collection
    {
        return $this->organizationUsers ?: $this->organizationUsers = new ArrayCollection();
    }

    public function getOrganizationUserNames(): array
    {
        $names = [];
        foreach ($this->getOrganizationUsers() as $orgUser) {
            $names[] = $orgUser->getUser()->getUserIdentifier();
        }

        return $names;
    }

    public function hasOrganizationUser(string $username): bool
    {
        return \in_array($username, $this->getOrganizationUserNames(), true);
    }

    public function addOrganizationUser(OrganizationUserInterface $organizationUser): self
    {
        if (!$this->isUserOrganization()
            && !$this->getOrganizationUsers()->contains($organizationUser)) {
            $this->getOrganizationUsers()->add($organizationUser);
        }

        return $this;
    }

    public function removeOrganizationUser(OrganizationUserInterface $organizationUser): self
    {
        if ($this->getOrganizationUsers()->contains($organizationUser)) {
            $this->getOrganizationUsers()->removeElement($organizationUser);
        }

        return $this;
    }
}
