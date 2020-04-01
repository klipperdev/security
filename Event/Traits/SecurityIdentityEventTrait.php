<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Event\Traits;

use Klipper\Component\Security\Identity\SecurityIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * The security identity event trait.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait SecurityIdentityEventTrait
{
    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * @var SecurityIdentityInterface[]
     */
    protected $securityIdentities = [];

    /**
     * Get the token.
     */
    public function getToken(): TokenInterface
    {
        return $this->token;
    }

    /**
     * Get security identities.
     *
     * @return SecurityIdentityInterface[]
     */
    public function getSecurityIdentities(): array
    {
        return $this->securityIdentities;
    }
}
