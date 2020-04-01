<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\ObjectFilter;

/**
 * Object Filter Voter Interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface ObjectFilterVoterInterface
{
    /**
     * Check if the value is supported by this voter.
     *
     * @param mixed $value
     */
    public function supports($value): bool;

    /**
     * Get the replacement value.
     *
     * @param mixed $value
     *
     * @return mixed The new value
     */
    public function getValue($value);
}
