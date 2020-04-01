<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Identity;

/**
 * Interface for events of security identities.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface CacheSecurityIdentityListenerInterface
{
    /**
     * Get the cache id for the event security identities.
     */
    public function getCacheId(): string;
}
