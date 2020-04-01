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

use Symfony\Contracts\EventDispatcher\Event;

/**
 * The abstract security event.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractSecurityEvent extends Event
{
    /**
     * @var bool
     */
    protected $permissionEnabled = true;

    /**
     * Check if the permission manager is enabled.
     */
    public function isPermissionEnabled(): bool
    {
        return $this->permissionEnabled;
    }
}
