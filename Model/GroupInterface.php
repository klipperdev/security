<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Model;

use Klipper\Component\Security\Model\Traits\RoleableInterface;

/**
 * User interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface GroupInterface extends RoleableInterface
{
    /**
     * Get the group name.
     *
     * @return string
     */
    public function getName(): ?string;

    /**
     * Set the group name.
     *
     * @param string $name The name
     *
     * @return static
     */
    public function setName(?string $name);
}
