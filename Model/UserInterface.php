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
use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;

/**
 * User interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface UserInterface extends BaseUserInterface, RoleableInterface
{
    /**
     * Get id.
     *
     * @return null|int|string
     */
    public function getId();
}
