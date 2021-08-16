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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Klipper\Component\Security\Model\SharingInterface;

/**
 * Trait of permission's sharing entries model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait PermissionSharingEntryTrait
{
    /**
     * @var Collection|SharingInterface[]
     *
     * @ORM\ManyToMany(
     *     targetEntity="Klipper\Component\Security\Model\SharingInterface",
     *     fetch="EXTRA_LAZY",
     *     mappedBy="permissions"
     * )
     */
    protected ?Collection $sharingEntries = null;

    public function getSharingEntries(): Collection
    {
        return $this->sharingEntries ?: $this->sharingEntries = new ArrayCollection();
    }
}
