<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Permission;

use Klipper\Component\Security\Exception\PermissionConfigNotFoundException;
use Klipper\Component\Security\Identity\SecurityIdentityInterface;
use Klipper\Component\Security\Identity\SubjectIdentityInterface;
use Klipper\Component\Security\Model\PermissionChecking;
use Klipper\Component\Security\Model\RoleInterface;

/**
 * Permission manager Interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface PermissionManagerInterface
{
    /**
     * Check if permission manager is disabled.
     *
     * If the permission manager is disabled, all asked authorizations will be
     * always accepted.
     *
     * If the permission manager is enabled, all asked authorizations will be accepted
     * depending on the permissions.
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Define if the permission manager is enable or not.
     *
     * @param bool $enabled The value
     *
     * @return static
     */
    public function setEnabled(bool $enabled);

    /**
     * Add the permission config.
     *
     * @param PermissionConfigInterface $config The permission config
     */
    public function addConfig(PermissionConfigInterface $config): void;

    /**
     * Check if the configuration of permission is present.
     *
     * @param string $class The class name
     *
     * @return bool
     */
    public function hasConfig(string $class): bool;

    /**
     * Get the configuration of permission.
     *
     * @param string $class The class name
     *
     * @throws PermissionConfigNotFoundException When the configuration of permission is not found
     *
     * @return PermissionConfigInterface
     */
    public function getConfig(string $class): PermissionConfigInterface;

    /**
     * Get the configurations of permissions.
     *
     * @return PermissionConfigInterface[]
     */
    public function getConfigs(): array;

    /**
     * Check if the subject is managed.
     *
     * @param FieldVote|object|string|SubjectIdentityInterface $subject The object or class name
     *
     * @return bool
     */
    public function isManaged($subject): bool;

    /**
     * Check if the field of subject is managed.
     *
     * @param object|string|SubjectIdentityInterface $subject The object or class name
     * @param string                                 $field   The field
     *
     * @return bool
     */
    public function isFieldManaged($subject, string $field): bool;

    /**
     * Determines whether access is granted.
     *
     * @param SecurityIdentityInterface[]                           $sids        The security identities
     * @param string|string[]                                       $permissions The permissions
     * @param null|FieldVote|object|string|SubjectIdentityInterface $subject     The object or class name or field vote
     *
     * @return bool
     */
    public function isGranted(array $sids, $permissions, $subject = null): bool;

    /**
     * Determines whether access is granted.
     *
     * @param SecurityIdentityInterface[]            $sids        The security identities
     * @param string|string[]                        $permissions The permissions
     * @param object|string|SubjectIdentityInterface $subject     The object or class name
     * @param string                                 $field       The field
     *
     * @return bool
     */
    public function isFieldGranted(array $sids, $permissions, $subject, string $field): bool;

    /**
     * Get the permissions of the role and subject.
     *
     * @param RoleInterface                                         $role    The role
     * @param null|FieldVote|object|string|SubjectIdentityInterface $subject The object or class name or field vote
     *
     * @return PermissionChecking[]
     */
    public function getRolePermissions(RoleInterface $role, $subject = null): array;

    /**
     * Get the permissions of the role and subject field.
     *
     * @param RoleInterface                          $role    The role
     * @param object|string|SubjectIdentityInterface $subject The object or class name
     * @param string                                 $field   The field
     *
     * @return PermissionChecking[]
     */
    public function getRoleFieldPermissions(RoleInterface $role, $subject, string $field): array;

    /**
     * Preload permissions of objects.
     *
     * @param object[] $objects The objects
     *
     * @return static
     */
    public function preloadPermissions(array $objects);

    /**
     * Reset the preload permissions for specific objects.
     *
     * @param object[] $objects The objects
     *
     * @return static
     */
    public function resetPreloadPermissions(array $objects);

    /**
     * Clear all permission caches.
     *
     * @return static
     */
    public function clear();
}
