<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Permission;

use Klipper\Component\Security\Exception\UnexpectedTypeException;
use Klipper\Component\Security\Identity\SubjectIdentityInterface;
use Klipper\Component\Security\Identity\SubjectUtils;

/**
 * Permission utils.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class PermissionUtils
{
    /**
     * Get the action for the map of permissions.
     *
     * @param null|string $action  The action
     * @param string      $default The default value
     *
     * @return string
     */
    public static function getMapAction(?string $action, string $default = '_global'): string
    {
        return $action ?? $default;
    }

    /**
     * Get the subject identity and field.
     *
     * @param null|FieldVote|object|string|SubjectIdentityInterface $subject  The subject instance or classname
     * @param bool                                                  $optional Check if the subject id optional
     *
     * @return array
     */
    public static function getSubjectAndField($subject, bool $optional = false): array
    {
        if ($subject instanceof FieldVote) {
            $field = $subject->getField();
            $subject = $subject->getSubject();
        } else {
            if (null === $subject && !$optional) {
                throw new UnexpectedTypeException($subject, 'FieldVote|SubjectIdentityInterface|object|string');
            }

            $field = null;
            $subject = null !== $subject
                ? SubjectUtils::getSubjectIdentity($subject)
                : null;
        }

        return [$subject, $field];
    }

    /**
     * Get the class and field.
     *
     * @param null|FieldVote|object|string|SubjectIdentityInterface $subject  The subject instance or classname
     * @param bool                                                  $optional Check if the subject id optional
     *
     * @return array
     */
    public static function getClassAndField($subject, bool $optional = false): array
    {
        /** @var null|SubjectIdentityInterface $subject */
        list($subject, $field) = static::getSubjectAndField($subject, $optional);

        $class = null !== $subject
            ? $subject->getType()
            : null;

        return [$class, $field];
    }
}
