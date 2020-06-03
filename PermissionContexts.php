<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class PermissionContexts
{
    /**
     * The PermissionContexts::ROLE context check if the permission
     * can be added on a role.
     */
    public const ROLE = 'role';

    /**
     * The PermissionContexts::ORGANIZATION_ROLE context check if the permission
     * can be added on a role of organization.
     *
     * In this case, the Role model must implement Klipper\Component\Security\Model\TraitsOrganizationalInterface
     */
    public const ORGANIZATION_ROLE = 'organization_role';

    /**
     * The PermissionContexts::SHARING context check if the permission
     * can be added on a sharing entry.
     */
    public const SHARING = 'sharing';
}
