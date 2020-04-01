<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Sharing;

/**
 * Sharing identity config Interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface SharingIdentityConfigInterface
{
    /**
     * Get the type. Typically, this is the PHP class name.
     */
    public function getType(): string;

    /**
     * Get the alias.
     */
    public function getAlias(): string;

    /**
     * Get the value of roleable.
     */
    public function getRoleable(): ?bool;

    /**
     * Check if the identity can be use the roles.
     */
    public function isRoleable(): bool;

    /**
     * Get the value of permissible.
     */
    public function getPermissible(): ?bool;

    /**
     * Check if the identity can be use the permissions.
     */
    public function isPermissible(): bool;

    /**
     * Merge the new sharing identity config.
     *
     * @param SharingIdentityConfigInterface $newConfig The new sharing identity config
     */
    public function merge(SharingIdentityConfigInterface $newConfig): void;
}
