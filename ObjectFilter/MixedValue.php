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
 * The Mixed Value Object Filter Voter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MixedValue implements ObjectFilterVoterInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($value): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($value)
    {
        return \is_array($value)
            ? []
            : null;
    }
}
