<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Model\Traits;

use Doctrine\Common\Collections\Collection;
use Klipper\Component\Security\Model\SharingInterface;

/**
 * Interface of permission's sharing entries model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface PermissionSharingEntryInterface
{
    /**
     * @return Collection|SharingInterface[]
     */
    public function getSharingEntries(): Collection;
}
