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

use Klipper\Component\Security\Identity\SecurityIdentityInterface;
use Klipper\Component\Security\Identity\SubjectIdentityInterface;
use Klipper\Component\Security\Model\RoleInterface;
use Klipper\Component\Security\Model\SharingInterface;

/**
 * Sharing provider Interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface SharingProviderInterface
{
    /**
     * Set the sharing manager.
     *
     * @param SharingManagerInterface $sharingManager The sharing manager
     *
     * @return static
     */
    public function setSharingManager(SharingManagerInterface $sharingManager);

    /**
     * Get the roles with permissions.
     *
     * @param string[] $roles The roles
     *
     * @return RoleInterface[]
     */
    public function getPermissionRoles(array $roles): array;

    /**
     * Get all permissions of subjects.
     *
     * @param SubjectIdentityInterface[]       $subjects The subjects
     * @param null|SecurityIdentityInterface[] $sids     The security identities to filter the sharing entries
     *
     * @return SharingInterface[]
     */
    public function getSharingEntries(array $subjects, $sids = null): array;

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
