<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Model\Traits;

use Klipper\Component\Security\Model\GroupInterface;

/**
 * Groupable interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface GroupableInterface
{
    /**
     * Indicates whether the model belongs to the specified group or not.
     *
     * @param string $name The name of the group
     */
    public function hasGroup(string $name): bool;

    /**
     * Gets the groups granted to the user.
     *
     * @return GroupInterface[]|\Traversable
     */
    public function getGroups();

    /**
     * Gets the name of the groups which includes the user.
     *
     * @return string[]
     */
    public function getGroupNames(): array;
}
