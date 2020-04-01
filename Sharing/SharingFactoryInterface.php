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
 * Sharing factory interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface SharingFactoryInterface
{
    /**
     * Create the sharing subject configurations.
     *
     * @throws
     */
    public function createSubjectConfigurations(): SharingSubjectConfigCollection;

    /**
     * Create the sharing identity configurations.
     *
     * @throws
     */
    public function createIdentityConfigurations(): SharingIdentityConfigCollection;
}
