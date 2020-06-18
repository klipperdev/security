<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Authorization\Voter;

use Klipper\Component\Security\Identity\SecurityIdentityInterface;
use Klipper\Component\Security\Identity\SecurityIdentityManagerInterface;
use Klipper\Component\Security\Organizational\OrganizationalUtil;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * AbstractIdentityVoter to determine the identities granted on current user defined in token.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractIdentityVoter extends Voter
{
    protected SecurityIdentityManagerInterface $sim;

    protected string $prefix;

    /**
     * @param SecurityIdentityManagerInterface $sim    The security identity manager
     * @param null|string                      $prefix The attribute prefix
     */
    public function __construct(SecurityIdentityManagerInterface $sim, ?string $prefix = null)
    {
        $this->sim = $sim;
        $this->prefix = $prefix ?? $this->getDefaultPrefix();
    }

    protected function supports(string $attribute, $subject): bool
    {
        return 0 === strpos($attribute, $this->prefix);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $sids = $this->sim->getSecurityIdentities($token);

        foreach ($sids as $sid) {
            if ($this->isValidIdentity($attribute, $sid)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the security identity is valid for this voter.
     *
     * @param string                    $attribute The attribute
     * @param SecurityIdentityInterface $sid       The security identity
     */
    protected function isValidIdentity(string $attribute, SecurityIdentityInterface $sid): bool
    {
        $type = $sid->getType();
        $isClass = class_exists($type) || interface_exists($type);

        return ($this->getValidType() === $type || ($isClass && \in_array($this->getValidType(), class_implements($type), true)))
            && substr($attribute, \strlen($this->prefix)) === OrganizationalUtil::format($sid->getIdentifier());
    }

    /**
     * Get the valid type of identity.
     */
    abstract protected function getValidType(): string;

    /**
     * Get the default prefix.
     */
    abstract protected function getDefaultPrefix(): string;
}
