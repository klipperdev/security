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

use Klipper\Component\Security\Event\AddSecurityIdentityEvent;
use Klipper\Component\Security\Event\PostSecurityIdentityEvent;
use Klipper\Component\Security\Event\PreSecurityIdentityEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Manager to retrieving security identities.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SecurityIdentityManager implements SecurityIdentityManagerInterface
{
    protected EventDispatcherInterface $dispatcher;

    protected RoleHierarchyInterface $roleHierarchy;

    protected AuthenticationTrustResolverInterface $authenticationTrustResolver;

    /**
     * @var string[]
     */
    protected array $roles = [];

    /**
     * @param EventDispatcherInterface             $dispatcher                  The event dispatcher
     * @param RoleHierarchyInterface               $roleHierarchy               The role hierarchy
     * @param AuthenticationTrustResolverInterface $authenticationTrustResolver The authentication trust resolver
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        RoleHierarchyInterface $roleHierarchy,
        AuthenticationTrustResolverInterface $authenticationTrustResolver
    ) {
        $this->dispatcher = $dispatcher;
        $this->roleHierarchy = $roleHierarchy;
        $this->authenticationTrustResolver = $authenticationTrustResolver;
    }

    public function addSpecialRole(string $role): SecurityIdentityManagerInterface
    {
        if (!isset($this->roles[$role])) {
            $this->roles[$role] = $role;
        }

        return $this;
    }

    public function getSecurityIdentities(?TokenInterface $token = null): array
    {
        $sids = [];

        if (null === $token) {
            return $sids;
        }

        // dispatch pre event
        $eventPre = new PreSecurityIdentityEvent($token, $sids);
        $this->dispatcher->dispatch($eventPre);

        // add current user and reachable roles
        $sids = $this->addCurrentUser($token, $sids);
        $sids = $this->addReachableRoles($token, $sids);

        // dispatch add event to add custom security identities
        $eventAdd = new AddSecurityIdentityEvent($token, $sids);
        $this->dispatcher->dispatch($eventAdd);
        $sids = $eventAdd->getSecurityIdentities();

        // add special roles
        $sids = $this->addSpecialRoles($token, $sids);

        // dispatch post event
        $eventPost = new PostSecurityIdentityEvent($token, $sids, $eventPre->isPermissionEnabled());
        $this->dispatcher->dispatch($eventPost);

        return $sids;
    }

    /**
     * Add the security identity of current user.
     *
     * @param null|TokenInterface         $token The token
     * @param SecurityIdentityInterface[] $sids  The security identities
     *
     * @return SecurityIdentityInterface[]
     */
    protected function addCurrentUser(?TokenInterface $token, array $sids): array
    {
        if (null !== $token) {
            try {
                $sids[] = UserSecurityIdentity::fromToken($token);
            } catch (\InvalidArgumentException $e) {
                // ignore, user has no user security identity
            }
        }

        return $sids;
    }

    /**
     * Add the security identities of reachable roles.
     *
     * @param TokenInterface              $token The token
     * @param SecurityIdentityInterface[] $sids  The security identities
     *
     * @return SecurityIdentityInterface[]
     */
    protected function addReachableRoles(TokenInterface $token, array $sids): array
    {
        foreach ($this->roleHierarchy->getReachableRoleNames($token->getRoleNames()) as $role) {
            $sids[] = RoleSecurityIdentity::fromAccount($role);
        }

        return $sids;
    }

    /**
     * Add the security identities of special roles.
     *
     * @param TokenInterface              $token The token
     * @param SecurityIdentityInterface[] $sids  The security identities
     *
     * @return SecurityIdentityInterface[]
     */
    protected function addSpecialRoles(TokenInterface $token, array $sids): array
    {
        $sids = $this->injectSpecialRoles($sids);

        if ($this->authenticationTrustResolver->isFullFledged($token)) {
            $sids[] = new RoleSecurityIdentity('role', AuthenticatedVoter::IS_AUTHENTICATED_FULLY);
            $sids[] = new RoleSecurityIdentity('role', AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
            $sids[] = new RoleSecurityIdentity('role', AuthenticatedVoter::IS_AUTHENTICATED);
        } elseif ($this->authenticationTrustResolver->isRememberMe($token)) {
            $sids[] = new RoleSecurityIdentity('role', AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
            $sids[] = new RoleSecurityIdentity('role', AuthenticatedVoter::IS_AUTHENTICATED);
        }

        $sids[] = new RoleSecurityIdentity('role', AuthenticatedVoter::PUBLIC_ACCESS);

        return $sids;
    }

    /**
     * Inject the special roles.
     *
     * @param SecurityIdentityInterface[] $sids The security identities
     *
     * @return SecurityIdentityInterface[]
     */
    private function injectSpecialRoles(array $sids): array
    {
        $roles = $this->getRoleNames($sids);

        foreach ($this->roles as $role) {
            if (!\in_array($role, $roles, true)) {
                $sids[] = RoleSecurityIdentity::fromAccount($role);
            }
        }

        return $sids;
    }

    /**
     * Get the role names of security identities.
     *
     * @param SecurityIdentityInterface[] $sids The security identities
     *
     * @return string[]
     */
    private function getRoleNames(array $sids): array
    {
        $roles = [];

        foreach ($sids as $sid) {
            if ($sid instanceof RoleSecurityIdentity) {
                $roles[] = $sid->getIdentifier();
            }
        }

        return $roles;
    }
}
