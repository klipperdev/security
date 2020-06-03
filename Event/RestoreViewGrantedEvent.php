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

use Klipper\Component\Security\Permission\FieldVote;

/**
 * The object field view granted event.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RestoreViewGrantedEvent extends AbstractViewGrantedEvent
{
    protected FieldVote $fieldVote;

    /**
     * @var mixed
     */
    protected $oldValue;

    /**
     * @var mixed
     */
    protected $newValue;

    /**
     * @param FieldVote $fieldVote The permission field vote
     * @param mixed     $oldValue  The old value of field
     * @param mixed     $newValue  The new value of field
     */
    public function __construct(FieldVote $fieldVote, $oldValue, $newValue)
    {
        parent::__construct($this->validateFieldVoteSubject($fieldVote));

        $this->fieldVote = $fieldVote;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }

    /**
     * Get the permission field vote.
     */
    public function getFieldVote(): FieldVote
    {
        return $this->fieldVote;
    }

    /**
     * Get the old value of field.
     *
     * @return mixed
     */
    public function getOldValue()
    {
        return $this->oldValue;
    }

    /**
     * Get the new value of field.
     *
     * @return mixed
     */
    public function getNewValue()
    {
        return $this->newValue;
    }
}
