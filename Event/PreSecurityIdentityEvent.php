<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Event;

use Klipper\Component\Security\Event\Traits\SecurityIdentityEventTrait;
use Klipper\Component\Security\Identity\SecurityIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * The pre security identity event.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PreSecurityIdentityEvent extends AbstractEditableSecurityEvent
{
    use SecurityIdentityEventTrait;

    /**
     * @param TokenInterface              $token              The token
     * @param SecurityIdentityInterface[] $securityIdentities The security identities
     */
    public function __construct(TokenInterface $token, array $securityIdentities = [])
    {
        $this->token = $token;
        $this->securityIdentities = $securityIdentities;
    }
}
