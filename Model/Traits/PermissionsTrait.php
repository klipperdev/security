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
use Klipper\Component\Security\Model\PermissionInterface;

/**
 * Trait of model with permissions.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait PermissionsTrait
{
    /**
     * @var null|Collection|PermissionInterface[]
     *
     * @ORM\ManyToMany(
     *     targetEntity="Klipper\Component\Security\Model\PermissionInterface",
     *     inversedBy="roles",
     *     cascade={"persist"}
     * )
     * @ORM\JoinTable(
     *     joinColumns={
     *         @ORM\JoinColumn(onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(onDelete="CASCADE", name="permission_id")
     *     }
     * )
     */
    protected ?Collection $permissions = null;

    public function getPermissions(): Collection
    {
        return $this->permissions ?: $this->permissions = new ArrayCollection();
    }

    public function hasPermission(PermissionInterface $permission): bool
    {
        return $this->getPermissions()->contains($permission);
    }

    public function addPermission(PermissionInterface $permission): self
    {
        if (!$this->getPermissions()->contains($permission)) {
            $this->getPermissions()->add($permission);
        }

        return $this;
    }

    public function removePermission(PermissionInterface $permission): self
    {
        if ($this->getPermissions()->contains($permission)) {
            $this->getPermissions()->removeElement($permission);
        }

        return $this;
    }
}
