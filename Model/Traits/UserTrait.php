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
use Klipper\Component\Security\Model\UserInterface;

/**
 * Trait for user model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait UserTrait
{
    /**
     * @ORM\Column(type="string", length=50, unique=true)
     */
    protected ?string $username = null;

    /**
     * @ORM\Column(type="string")
     */
    protected ?string $password = null;

    /**
     * Set the user identifier.
     *
     * @param null|string $userIdentifier The user identifier
     *
     * @return static
     */
    public function setUserIdentifier(?string $userIdentifier): self
    {
        $this->username = $userIdentifier;

        return $this;
    }

    public function getUserIdentifier(): ?string
    {
        return $this->username;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface::getUsername()
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Set the username.
     *
     * @param null|string $username The username
     *
     * @return static
     */
    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface::getPassword()
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface::getSalt()
     */
    public function getSalt(): ?string
    {
        // not needed when using the "bcrypt" algorithm in security.yaml

        return null;
    }

    /**
     * @see UserInterface::eraseCredentials()
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
