<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Event\Traits;

/**
 * This is a general purpose reachable role event.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait ReachableRoleEventTrait
{
    /**
     * @var string[]
     */
    protected $reachableRoles = [];

    /**
     * Set reachable roles.
     *
     * @param string[] $reachableRoles
     */
    public function setReachableRoleNames(array $reachableRoles): void
    {
        $this->reachableRoles = $reachableRoles;
    }

    /**
     * Get reachable roles.
     *
     * @return string[]
     */
    public function getReachableRoleNames(): array
    {
        return $this->reachableRoles;
    }
}
