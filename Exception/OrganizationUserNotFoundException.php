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
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationUserNotFoundException extends OrganizationalException
{
    public function __construct(
        $message = 'Organization name with user identifier could not be found.',
        $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
