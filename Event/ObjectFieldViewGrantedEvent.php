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
class ObjectFieldViewGrantedEvent extends AbstractViewGrantedEvent
{
    protected FieldVote $fieldVote;

    /**
     * @param FieldVote $fieldVote The permission field vote
     */
    public function __construct(FieldVote $fieldVote)
    {
        parent::__construct($this->validateFieldVoteSubject($fieldVote));

        $this->fieldVote = $fieldVote;
    }

    /**
     * Get the permission field vote.
     */
    public function getFieldVote(): FieldVote
    {
        return $this->fieldVote;
    }
}
