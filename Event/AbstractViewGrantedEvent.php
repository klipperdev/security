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

use Klipper\Component\Security\Exception\UnexpectedTypeException;
use Klipper\Component\Security\Permission\FieldVote;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The abstract view granted event.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractViewGrantedEvent extends Event
{
    protected object $object;

    protected bool $isGranted = true;

    protected bool $skip = false;

    public function __construct(object $object)
    {
        $this->object = $object;
    }

    /**
     * Get the object.
     */
    public function getObject(): object
    {
        return $this->object;
    }

    /**
     * Defined if the user has the view access of this object.
     *
     * @param bool $isGranted The granted value
     *
     * @return static
     */
    public function setGranted(bool $isGranted): self
    {
        $this->isGranted = $isGranted;
        $this->skipAuthorizationChecker(true);

        return $this;
    }

    /**
     * Check if the user has the view access of this object.
     */
    public function isGranted(): bool
    {
        return $this->isGranted;
    }

    /**
     * Skip the permission authorization checker or not.
     *
     * @param bool $skip The value
     *
     * @return static
     */
    public function skipAuthorizationChecker(bool $skip): self
    {
        $this->skip = $skip;

        return $this;
    }

    /**
     * Check if the permission authorization checker must be skipped or not.
     */
    public function isSkipAuthorizationChecker(): bool
    {
        return $this->skip;
    }

    /**
     * Validate and return the domain object instance in field vote.
     */
    protected function validateFieldVoteSubject(FieldVote $fieldVote): object
    {
        $object = $fieldVote->getSubject()->getObject();

        if (!\is_object($object)) {
            throw new UnexpectedTypeException($object, 'object');
        }

        return $object;
    }
}
