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

use Symfony\Contracts\EventDispatcher\Event;

/**
 * The abstract commit object filter event.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractCommitObjectFilterEvent extends Event
{
    /**
     * @var object[]
     */
    protected array $objects;

    /**
     * @param object[] $objects The objects
     */
    public function __construct(array $objects)
    {
        $this->objects = $objects;
    }

    /**
     * Get the objects.
     *
     * @return object[]
     */
    public function getObjects(): array
    {
        return $this->objects;
    }
}
