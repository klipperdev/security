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

use Klipper\Component\Security\Identity\SubjectIdentityInterface;
use Klipper\Component\Security\Model\PermissionInterface;

/**
 * Permission provider Interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface PermissionProviderInterface
{
    public const CONFIG_CLASS = '_config_class';

    public const CONFIG_FIELD = '_config_field';

    /**
     * Get all permissions of roles.
     *
     * @param string[] $roles The roles
     *
     * @return PermissionInterface[]
     */
    public function getPermissions(array $roles): array;

    /**
     * Get the permissions by subject.
     *
     * @param null|FieldVote|object|string|SubjectIdentityInterface $subject  The subject instance or classname
     * @param null|string|string[]                                  $contexts The permission contexts
     *
     * @return PermissionInterface[]
     */
    public function getPermissionsBySubject($subject = null, $contexts = null): array;

    /**
     * Get the config permissions.
     *
     * @param null|string|string[] $contexts The permission contexts
     *
     * @return PermissionInterface[]
     */
    public function getConfigPermissions($contexts = null): array;

    /**
     * Get the class name of association field.
     *
     * @param PermissionConfigInterface $config The permission config
     *
     * @return null|string
     */
    public function getMasterClass(PermissionConfigInterface $config): ?string;
}
