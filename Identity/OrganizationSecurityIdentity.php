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

use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Klipper\Component\Security\Model\GroupInterface;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\Model\Traits\GroupableInterface;
use Klipper\Component\Security\Model\Traits\RoleableInterface;
use Klipper\Component\Security\Model\Traits\UserOrganizationUsersInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class OrganizationSecurityIdentity extends AbstractSecurityIdentity
{
    /**
     * Creates a organization security identity from a OrganizationInterface.
     *
     * @param OrganizationInterface $organization The organization
     *
     * @return static
     */
    public static function fromAccount(OrganizationInterface $organization): self
    {
        return new self(ClassUtils::getClass($organization), $organization->getName());
    }

    /**
     * Creates a organization security identity from a TokenInterface.
     *
     * @param TokenInterface                      $token         The token
     * @param null|OrganizationalContextInterface $context       The organizational context
     * @param null|RoleHierarchyInterface         $roleHierarchy The role hierarchy
     *
     * @return SecurityIdentityInterface[]
     */
    public static function fromToken(
        TokenInterface $token,
        ?OrganizationalContextInterface $context = null,
        ?RoleHierarchyInterface $roleHierarchy = null
    ): array {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return [];
        }

        return null !== $context
            ? static::getSecurityIdentityForCurrentOrganization($context, $roleHierarchy)
            : static::getSecurityIdentityForAllOrganizations($user, $roleHierarchy);
    }

    /**
     * Get the security identities for all organizations of user.
     *
     * @param UserInterface               $user          The user
     * @param null|RoleHierarchyInterface $roleHierarchy The role hierarchy
     *
     * @return SecurityIdentityInterface[]
     */
    protected static function getSecurityIdentityForAllOrganizations(UserInterface $user, ?RoleHierarchyInterface $roleHierarchy = null): array
    {
        $sids = [];

        if ($user instanceof UserOrganizationUsersInterface) {
            foreach ($user->getUserOrganizations() as $userOrg) {
                $sids[] = [self::fromAccount($userOrg->getOrganization())];
                $sids[] = static::getOrganizationGroups($userOrg);
                $roles = static::getOrganizationUserRoles($userOrg, $roleHierarchy);

                foreach ($roles as $role) {
                    $sids[] = [RoleSecurityIdentity::fromAccount($role)];
                }
            }
        }

        return \count($sids) > 0 ? array_merge(...$sids) : [];
    }

    /**
     * Get the security identities for the current organization of user.
     *
     * @param OrganizationalContextInterface $context       The organizational context
     * @param null|RoleHierarchyInterface    $roleHierarchy The role hierarchy
     *
     * @return SecurityIdentityInterface[]
     */
    protected static function getSecurityIdentityForCurrentOrganization(
        OrganizationalContextInterface $context,
        ?RoleHierarchyInterface $roleHierarchy = null
    ): array {
        $sids = [];
        $org = $context->getCurrentOrganization();
        $userOrg = $context->getCurrentOrganizationUser();
        $orgRoles = [];

        if ($org) {
            $sids[] = self::fromAccount($org);
        }

        if (null !== $userOrg) {
            $sids = array_merge($sids, static::getOrganizationGroups($userOrg));
            $orgRoles = static::getOrganizationUserRoles($userOrg, $roleHierarchy);
        } elseif ($org && $org->isUserOrganization()) {
            $orgRoles = static::getOrganizationRoles($org, $roleHierarchy);
        }

        foreach ($orgRoles as $role) {
            $sids[] = RoleSecurityIdentity::fromAccount($role);
        }

        return $sids;
    }

    /**
     * Get the security identities for organization groups of user.
     *
     * @param OrganizationUserInterface $user The organization user
     *
     * @return GroupSecurityIdentity[]
     */
    protected static function getOrganizationGroups(OrganizationUserInterface $user): array
    {
        $sids = [];
        $orgName = $user->getOrganization() ? $user->getOrganization()->getName() : null;

        if (null !== $orgName && $user instanceof GroupableInterface) {
            foreach ($user->getGroups() as $group) {
                if ($group instanceof GroupInterface) {
                    $sids[] = new GroupSecurityIdentity(ClassUtils::getClass($group), $group->getName().'__'.$orgName);
                }
            }
        }

        return $sids;
    }

    /**
     * Get the organization roles.
     *
     * @param OrganizationInterface       $organization  The organization
     * @param null|RoleHierarchyInterface $roleHierarchy The role hierarchy
     *
     * @return string[]
     */
    protected static function getOrganizationRoles(OrganizationInterface $organization, ?RoleHierarchyInterface $roleHierarchy = null): array
    {
        $roles = [];

        if ($organization instanceof RoleableInterface && $organization instanceof OrganizationInterface) {
            $roles = self::buildOrganizationRoles([], $organization);

            if ($roleHierarchy instanceof RoleHierarchyInterface) {
                $roles = $roleHierarchy->getReachableRoleNames($roles);
            }
        }

        return $roles;
    }

    /**
     * Get the organization roles of user.
     *
     * @param OrganizationUserInterface   $user          The organization user
     * @param null|RoleHierarchyInterface $roleHierarchy The role hierarchy
     *
     * @return string[]
     */
    protected static function getOrganizationUserRoles(OrganizationUserInterface $user, ?RoleHierarchyInterface $roleHierarchy = null): array
    {
        $roles = [];

        if ($user instanceof RoleableInterface && $user instanceof OrganizationUserInterface) {
            $org = $user->getOrganization();

            if ($org) {
                $roles = self::buildOrganizationUserRoles($roles, $user, $org->getName());
                $roles = self::buildOrganizationRoles($roles, $org);
            }

            if ($roleHierarchy instanceof RoleHierarchyInterface) {
                $roles = $roleHierarchy->getReachableRoleNames($roles);
            }
        }

        return $roles;
    }

    /**
     * Build the organization user roles.
     *
     * @param string[]          $roles   The roles
     * @param RoleableInterface $user    The organization user
     * @param string            $orgName The organization name
     *
     * @return string[]
     */
    private static function buildOrganizationUserRoles(array $roles, RoleableInterface $user, string $orgName): array
    {
        foreach ($user->getRoles() as $role) {
            $roles[] = $role.'__'.$orgName;
        }

        return $roles;
    }

    /**
     * Build the user organization roles.
     *
     * @param string[]              $roles The roles
     * @param OrganizationInterface $org   The organization of user
     *
     * @return string[]
     */
    private static function buildOrganizationRoles(array $roles, OrganizationInterface $org): array
    {
        $orgName = $org->getName();

        if ($org instanceof RoleableInterface) {
            $existingRoles = [];

            foreach ($roles as $role) {
                $existingRoles[] = $role;
            }

            foreach ($org->getRoles() as $orgRole) {
                $roleName = $orgRole;

                if (!\in_array($roleName, $existingRoles, true)) {
                    $roles[] = $roleName.'__'.$orgName;
                    $existingRoles[] = $roleName;
                }
            }
        }

        return $roles;
    }
}
