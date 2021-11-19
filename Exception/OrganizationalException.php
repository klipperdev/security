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
class OrganizationalException extends RuntimeException
{
    public function __construct(
        $message = 'Organizational request could not be processed due to a system problem.',
        $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
