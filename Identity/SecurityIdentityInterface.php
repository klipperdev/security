<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Identity;

/**
 * This interface provides an additional level of indirection,
 * so that we can work with abstracted versions of security objects.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface SecurityIdentityInterface
{
    /**
     * Get the identity type.
     */
    public function getType(): string;

    /**
     * Get the identifier.
     * Typically, the name of subject.
     */
    public function getIdentifier(): string;

    /**
     * This method is used to compare two security identities in order to
     * not rely on referential equality.
     */
    public function equals(SecurityIdentityInterface $identity): bool;
}
