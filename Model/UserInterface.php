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

    /**
     * Sets the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return static
     */
    public function setPassword(?string $password);

    /**
     * Get the user identifier.
     */
    public function getUserIdentifier(): ?string;

    /**
     * Sets the user identifier used to authenticate the user.
     *
     * @return static
     */
    public function setUserIdentifier(?string $userIdentifier);

    /**
     * Sets the username used to authenticate the user.
     *
     * @return static
     */
    public function setUsername(?string $username);
}
