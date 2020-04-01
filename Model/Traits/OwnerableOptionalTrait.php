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
 * Trait of add dependency entity with an optional user.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait OwnerableOptionalTrait
{
    /**
     * @var null|UserInterface
     */
    protected $owner;

    /**
     * {@inheritdoc}
     */
    public function setOwner(?UserInterface $user): self
    {
        $this->owner = $user;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwner(): ?UserInterface
    {
        return $this->owner;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwnerId()
    {
        return null !== $this->getOwner()
            ? $this->getOwner()->getId()
            : null;
    }
}
