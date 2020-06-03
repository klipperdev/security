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

use Klipper\Component\Security\Model\UserInterface;

/**
 * Trait of add dependency entity with an user.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait OwnerableTrait
{
    protected ?UserInterface $owner = null;

    public function setOwner(?UserInterface $user): self
    {
        $this->owner = $user;

        return $this;
    }

    public function getOwner(): ?UserInterface
    {
        return $this->owner;
    }

    public function getOwnerId()
    {
        return null !== $this->getOwner()
            ? $this->getOwner()->getId()
            : null;
    }
}
