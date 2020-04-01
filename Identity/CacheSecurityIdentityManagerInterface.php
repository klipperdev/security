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
 * Interface to retrieving security identities from tokens with caching.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface CacheSecurityIdentityManagerInterface extends SecurityIdentityManagerInterface
{
    /**
     * Invalidate the execution cache.
     */
    public function invalidateCache(): void;
}
