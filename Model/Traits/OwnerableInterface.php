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
 * Interface of add dependency entity with an user.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface OwnerableInterface
{
    /**
     * Set the owner.
     *
     * @param UserInterface $user The user
     *
     * @return static
     */
    public function setOwner(UserInterface $user);

    /**
     * Get the owner.
     */
    public function getOwner(): ?UserInterface;

    /**
     * Get the owner id.
     *
     * @return null|int|string
     */
    public function getOwnerId();
}
