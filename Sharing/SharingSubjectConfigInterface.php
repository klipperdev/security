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
 * Sharing subject config Interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface SharingSubjectConfigInterface
{
    /**
     * Get the type. Typically, this is the PHP class name.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Get the sharing visibility.
     *
     * @return string
     */
    public function getVisibility(): string;

    /**
     * Merge the new sharing subject config.
     *
     * @param SharingSubjectConfigInterface $newConfig The new sharing subject config
     */
    public function merge(SharingSubjectConfigInterface $newConfig): void;
}
