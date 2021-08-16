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

use Doctrine\ORM\Mapping as ORM;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\UserInterface;

/**
 * Trait for organization user model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait OrganizationUserTrait
{
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Klipper\Component\Security\Model\OrganizationInterface",
     *     inversedBy="organizationUsers"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    protected ?OrganizationInterface $organization = null;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Klipper\Component\Security\Model\UserInterface",
     *     inversedBy="userOrganizations",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected ?UserInterface $user = null;

    public function __toString(): string
    {
        return $this->organization->getName().':'.$this->user->getUserIdentifier();
    }

    public function setOrganization(?OrganizationInterface $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getOrganization(): ?OrganizationInterface
    {
        return $this->organization;
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
}
