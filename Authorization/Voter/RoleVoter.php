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

use Klipper\Component\Security\Identity\RoleSecurityIdentity;
use Klipper\Component\Security\Identity\SecurityIdentityManagerInterface;
use Klipper\Component\Security\Organizational\OrganizationalUtil;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter as BaseRoleVoter;

/**
 * Role Voter uses a SecurityIdentityManager to
 * determine the roles granted to the user before voting.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RoleVoter extends BaseRoleVoter
{
    /**
     * @var SecurityIdentityManagerInterface
     */
    private $sim;

    /**
     * @var array
     */
    private $cacheExec;

    /**
     * Constructor.
     *
     * @param SecurityIdentityManagerInterface $sim    The security identity manager
     * @param string                           $prefix The role prefix
     */
    public function __construct(SecurityIdentityManagerInterface $sim, string $prefix = 'ROLE_')
    {
        $this->sim = $sim;
        $this->cacheExec = [];

        parent::__construct($prefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function extractRoles(TokenInterface $token)
    {
        $sids = $this->sim->getSecurityIdentities($token);
        $id = sha1(implode('|', $sids));

        if (isset($this->cacheExec[$id])) {
            return $this->cacheExec[$id];
        }

        $roles = [];

        foreach ($sids as $sid) {
            if ($sid instanceof RoleSecurityIdentity) {
                $roles[] = OrganizationalUtil::format($sid->getIdentifier());
            }
        }

        return $this->cacheExec[$id] = $roles;
    }
}
