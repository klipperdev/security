<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Doctrine;

use Klipper\Component\Security\Doctrine\ORM\Event\GetNoneFilterEvent;
use Klipper\Component\Security\Doctrine\ORM\Event\GetPrivateFilterEvent;
use Klipper\Component\Security\Doctrine\ORM\Event\GetPublicFilterEvent;
use Klipper\Component\Security\SharingVisibilities;

/**
 * The doctrine sharing visibilities.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class DoctrineSharingVisibilities
{
    public static array $classMap = [
        SharingVisibilities::TYPE_NONE => GetNoneFilterEvent::class,
        SharingVisibilities::TYPE_PUBLIC => GetPublicFilterEvent::class,
        SharingVisibilities::TYPE_PRIVATE => GetPrivateFilterEvent::class,
    ];
}
