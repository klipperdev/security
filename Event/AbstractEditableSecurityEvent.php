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

/**
 * The abstract editable security event.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractEditableSecurityEvent extends AbstractSecurityEvent
{
    /**
     * Defined if the permission manager must be enable or not.
     *
     * @param bool $enabled The value
     */
    public function setPermissionEnabled(bool $enabled): void
    {
        $this->permissionEnabled = $enabled;
    }
}
