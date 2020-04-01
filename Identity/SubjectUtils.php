<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Identity;

use Klipper\Component\Security\Exception\UnexpectedTypeException;

/**
 * Subject utils.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class SubjectUtils
{
    /**
     * Get the subject identity.
     *
     * @param object|string|SubjectIdentityInterface $subject The subject instance or classname
     *
     * @return SubjectIdentityInterface
     */
    public static function getSubjectIdentity($subject): SubjectIdentityInterface
    {
        if ($subject instanceof SubjectIdentityInterface) {
            return $subject;
        }

        if (\is_string($subject)) {
            return SubjectIdentity::fromClassname($subject);
        }

        if (\is_object($subject)) {
            return SubjectIdentity::fromObject($subject);
        }

        throw new UnexpectedTypeException($subject, SubjectIdentityInterface::class.'|object|string');
    }
}
