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
     *     mappedBy="organization",
     *     fetch="EXTRA_LAZY",
     *     cascade={"persist", "remove"}
     * )
     */
    protected ?Collection $organizationUsers = null;

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return (string) $this->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function isUserOrganization(): bool
    {
        return null !== $this->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationUsers(): Collection
    {
        return $this->organizationUsers ?: $this->organizationUsers = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationUserNames(): array
    {
        $names = [];
        foreach ($this->getOrganizationUsers() as $orgUser) {
            $names[] = $orgUser->getUser()->getUsername();
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOrganizationUser(string $username): bool
    {
        return \in_array($username, $this->getOrganizationUserNames(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function addOrganizationUser(OrganizationUserInterface $organizationUser): self
    {
        if (!$this->isUserOrganization()
            && !$this->getOrganizationUsers()->contains($organizationUser)) {
            $this->getOrganizationUsers()->add($organizationUser);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeOrganizationUser(OrganizationUserInterface $organizationUser): self
    {
        if ($this->getOrganizationUsers()->contains($organizationUser)) {
            $this->getOrganizationUsers()->removeElement($organizationUser);
        }

        return $this;
    }
}
