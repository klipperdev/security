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

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Interface to retrieving security identities from tokens.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface SecurityIdentityManagerInterface
{
    /**
     * Add the special role.
     *
     * @param string $role The special role
     *
     * @return SecurityIdentityManagerInterface
     */
    public function addSpecialRole(string $role): SecurityIdentityManagerInterface;

    /**
     * Retrieves the available security identities for the given token.
     *
     * @param null|TokenInterface $token The token
     *
     * @return SecurityIdentityInterface[] The security identities
     */
    public function getSecurityIdentities(?TokenInterface $token = null): array;
}
