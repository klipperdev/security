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
 * This exception is thrown when SubjectIdentity fails to construct an subject
 * identity from the passed object.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class InvalidSubjectIdentityException extends RuntimeException
{
}
