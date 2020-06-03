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

use Klipper\Component\Security\Event\Traits\ReachableRoleEventTrait;

/**
 * The post reachable role event.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PostReachableRoleEvent extends AbstractSecurityEvent
{
    use ReachableRoleEventTrait;

    /**
     * @param string[] $reachableRoles    The reachable roles
     * @param bool     $permissionEnabled Check if the permission manager is enabled
     */
    public function __construct(array $reachableRoles, bool $permissionEnabled = true)
    {
        $this->reachableRoles = $reachableRoles;
        $this->permissionEnabled = $permissionEnabled;
    }
}
