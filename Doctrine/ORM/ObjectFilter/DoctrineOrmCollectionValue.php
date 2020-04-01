<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Doctrine\ORM\ObjectFilter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Klipper\Component\Security\ObjectFilter\ObjectFilterVoterInterface;

/**
 * The Doctrine Orm Collection Value Object Filter Voter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class DoctrineOrmCollectionValue implements ObjectFilterVoterInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($value): bool
    {
        return $value instanceof Collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($value)
    {
        return new ArrayCollection();
    }
}
