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
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractSecurityIdentity extends AbstractBaseIdentity implements SecurityIdentityInterface
{
    /**
     * A textual representation of this security identity.
     *
     * This is not used for equality comparison, but only for debugging.
     *
     * @throws
     */
    public function __toString(): string
    {
        $name = (new \ReflectionClass($this))->getShortName();

        return sprintf('%s(%s)', $name, $this->getIdentifier());
    }

    public function equals(SecurityIdentityInterface $identity): bool
    {
        if (!$identity instanceof self || $this->getType() !== $identity->getType()) {
            return false;
        }

        return $this->getIdentifier() === $identity->getIdentifier();
    }
}
