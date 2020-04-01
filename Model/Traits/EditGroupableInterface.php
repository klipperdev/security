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
 * Edit Groupable interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface EditGroupableInterface extends GroupableInterface
{
    /**
     * Add a group to the user groups.
     *
     * @return static
     */
    public function addGroup(GroupInterface $group);

    /**
     * Remove a group from the user groups.
     *
     * @return static
     */
    public function removeGroup(GroupInterface $group);
}
