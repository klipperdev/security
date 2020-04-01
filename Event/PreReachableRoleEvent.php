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
 * The pre reachable role event.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PreReachableRoleEvent extends AbstractEditableSecurityEvent
{
    use ReachableRoleEventTrait;

    /**
     * Constructor.
     *
     * @param string[] $reachableRoles The reachable roles
     */
    public function __construct(array $reachableRoles)
    {
        $this->reachableRoles = $reachableRoles;
    }
}
