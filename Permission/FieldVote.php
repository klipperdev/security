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

use Klipper\Component\Security\Identity\SubjectIdentityInterface;
use Klipper\Component\Security\Identity\SubjectUtils;

/**
 * Field vote.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FieldVote
{
    /**
     * @var SubjectIdentityInterface
     */
    private $subject;

    /**
     * @var string
     */
    private $field;

    /**
     * Constructor.
     *
     * @param object|string|SubjectIdentityInterface $subject The subject instance or classname
     * @param string                                 $field   The field name
     */
    public function __construct($subject, string $field)
    {
        $this->subject = SubjectUtils::getSubjectIdentity($subject);
        $this->field = $field;
    }

    /**
     * Get the subject.
     *
     * @return SubjectIdentityInterface
     */
    public function getSubject(): SubjectIdentityInterface
    {
        return $this->subject;
    }

    /**
     * Get the field name.
     *
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }
}
