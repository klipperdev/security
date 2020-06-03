<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class SharingVisibilities
{
    /**
     * The SharingVisibilities::TYPE_NONE type defines that no record is filtered and configured.
     */
    public const TYPE_NONE = 'none';

    /**
     * The SharingVisibilities::TYPE_PUBLIC type defines that no record is filtered, but records
     * can be configured.
     */
    public const TYPE_PUBLIC = 'public';

    /**
     * The SharingVisibilities::TYPE_PRIVATE type defines that records are filtered,
     * and only records with sharing entries are listed with their configurations.
     */
    public const TYPE_PRIVATE = 'private';
}
