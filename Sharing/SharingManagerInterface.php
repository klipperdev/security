<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Sharing;

use Klipper\Component\Security\Identity\SubjectIdentityInterface;

/**
 * Sharing manager Interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface SharingManagerInterface
{
    /**
     * Check if sharing manager is enabled.
     *
     * If the sharing manager is disabled, all sharing visibilities are the value NONE.
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Define if the sharing manager is enable or not.
     *
     * @param bool $enabled The value
     *
     * @return static
     */
    public function setEnabled(bool $enabled);

    /**
     * Add the sharing subject config.
     *
     * @param SharingSubjectConfigInterface $config The sharing subject config
     *
     * @return static
     */
    public function addSubjectConfig(SharingSubjectConfigInterface $config);

    /**
     * Check if the sharing subject config is present.
     *
     * @param string $class The class name of sharing subject
     *
     * @return bool
     */
    public function hasSubjectConfig(string $class): bool;

    /**
     * Get the sharing subject config.
     *
     * @param string $class The class name of sharing subject
     *
     * @return SharingSubjectConfigInterface
     */
    public function getSubjectConfig(string $class): SharingSubjectConfigInterface;

    /**
     * Get the sharing subject configs.
     *
     * @return SharingSubjectConfigInterface[]
     */
    public function getSubjectConfigs(): array;

    /**
     * Check if the subject has sharing visibility of subject identity.
     *
     * @param SubjectIdentityInterface $subject The subject
     *
     * @return bool
     */
    public function hasSharingVisibility(SubjectIdentityInterface $subject): bool;

    /**
     * Get the sharing visibility of subject identity.
     *
     * @param SubjectIdentityInterface $subject The subject
     *
     * @return string
     */
    public function getSharingVisibility(SubjectIdentityInterface $subject): string;

    /**
     * Add the sharing identity config.
     *
     * @param SharingIdentityConfigInterface $config The sharing identity config
     *
     * @return static
     */
    public function addIdentityConfig(SharingIdentityConfigInterface $config);

    /**
     * Check if the sharing identity config is present.
     *
     * @param string $class The class name of sharing identity
     *
     * @return bool
     */
    public function hasIdentityConfig(string $class): bool;

    /**
     * Get the sharing identity config.
     *
     * @param string $class The class name of sharing identity
     *
     * @return SharingIdentityConfigInterface
     */
    public function getIdentityConfig(string $class): SharingIdentityConfigInterface;

    /**
     * Get the sharing identity configs.
     *
     * @return SharingIdentityConfigInterface[]
     */
    public function getIdentityConfigs(): array;

    /**
     * Check if there is an identity config with the roleable option.
     *
     * @return bool
     */
    public function hasIdentityRoleable(): bool;

    /**
     * Check if there is an identity config with the permissible option.
     *
     * @return bool
     */
    public function hasIdentityPermissible(): bool;

    /**
     * Check if the access is granted by a sharing entry.
     *
     * @param string                        $operation The operation
     * @param null|SubjectIdentityInterface $subject   The subject
     * @param null|string                   $field     The field of subject
     *
     * @return bool
     */
    public function isGranted(string $operation, ?SubjectIdentityInterface $subject = null, ?string $field = null): bool;

    /**
     * Preload permissions of objects.
     *
     * @param object[] $objects The objects
     *
     * @return static
     */
    public function preloadPermissions(array $objects);

    /**
     * Preload the permissions of sharing roles.
     *
     * @param SubjectIdentityInterface[] $subjects The subjects
     */
    public function preloadRolePermissions(array $subjects);

    /**
     * Reset the preload permissions of objects.
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

    /**
     * Rename the identity of sharing.
     *
     * @param string $type    The identity type. Typically the PHP class name
     * @param string $oldName The old identity name
     * @param string $newName The new identity name
     *
     * @return static
     */
    public function renameIdentity(string $type, string $oldName, string $newName);

    /**
     * Delete the identity of sharing.
     *
     * @param string $type The identity type. Typically the PHP class name
     * @param string $name The identity name
     *
     * @return static
     */
    public function deleteIdentity(string $type, string $name);

    /**
     * Delete the sharing entry with ids.
     *
     * @param array $ids The sharing ids
     *
     * @return static
     */
    public function deletes(array $ids);
}
