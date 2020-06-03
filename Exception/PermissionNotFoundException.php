<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Exception;

/**
 * PermissionNotFoundException for the Security component.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PermissionNotFoundException extends InvalidArgumentException
{
    /**
     * @param string      $operation The permission operation
     * @param string      $class     The class name
     * @param null|string $field     The field name
     */
    public function __construct(string $operation, string $class, ?string $field = null)
    {
        $msg = 'The permission "%s" for "%s%s" is not found ant it required by the permission configuration';
        $msg = sprintf($msg, $operation, $class, null === $field ? '' : '::'.$field);

        parent::__construct($msg);
    }
}
