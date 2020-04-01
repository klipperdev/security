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

/**
 * This method can be implemented by domain objects which you want to store
 * permissions for if they do not have a getId() method, or getId() does not return
 * a unique identifier.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface SubjectInterface
{
    /**
     * Get the unique identifier for this subject.
     *
     * @return int|string
     */
    public function getSubjectIdentifier();
}
