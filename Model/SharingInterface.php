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

use Klipper\Component\Security\Model\Traits\PermissionsInterface;
use Klipper\Component\Security\Model\Traits\RoleableInterface;

/**
 * Sharing interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface SharingInterface extends PermissionsInterface, RoleableInterface
{
    /**
     * Get the id.
     *
     * @return null|int|string
     */
    public function getId();

    /**
     * Set the classname of subject.
     *
     * @param null|string $class The classname
     *
     * @return static
     */
    public function setSubjectClass(?string $class);

    /**
     * Get the classname of subject.
     *
     * @return null|string
     */
    public function getSubjectClass(): ?string;

    /**
     * Set the id of subject.
     *
     * @param null|string $id The id
     *
     * @return static
     */
    public function setSubjectId(?string $id);

    /**
     * Get the id of subject.
     *
     * @return null|string
     */
    public function getSubjectId(): ?string;

    /**
     * Set the classname of identity.
     *
     * @param null|string $class The classname
     *
     * @return static
     */
    public function setIdentityClass(?string $class);

    /**
     * Get the classname of identity.
     *
     * @return null|string
     */
    public function getIdentityClass(): ?string;

    /**
     * Set the unique name of identity.
     *
     * @param null|string $name The unique name
     *
     * @return static
     */
    public function setIdentityName(?string $name);

    /**
     * Get the unique name of identity.
     *
     * @return null|string
     */
    public function getIdentityName(): ?string;

    /**
     * Define if the sharing entry is enabled.
     *
     * @param bool $enabled The value
     *
     * @return static
     */
    public function setEnabled(bool $enabled);

    /**
     * Check if the sharing entry is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Set the date when the sharing entry must start.
     *
     * @param null|\DateTime $date The date
     *
     * @return static
     */
    public function setStartedAt(?\DateTime $date);

    /**
     * Get the date when the sharing entry must start.
     *
     * @return null|\DateTime
     */
    public function getStartedAt(): ?\DateTime;

    /**
     * Set the date when the sharing entry must end.
     *
     * @param null|\DateTime $date The date
     *
     * @return static
     */
    public function setEndedAt(?\DateTime $date);

    /**
     * Get the date when the sharing entry must end.
     *
     * @return null|\DateTime
     */
    public function getEndedAt(): ?\DateTime;
}
