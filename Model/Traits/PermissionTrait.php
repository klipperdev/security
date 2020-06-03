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
use Klipper\Component\Security\Model\RoleInterface;

/**
 * Trait of permission model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait PermissionTrait
{
    /**
     * @var string[]
     *
     * @ORM\Column(type="json", nullable=true)
     */
    protected array $contexts = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $class = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $field = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected ?string $operation = null;

    /**
     * @var null|Collection|RoleInterface[]
     *
     * @ORM\ManyToMany(
     *     targetEntity="Klipper\Component\Security\Model\RoleInterface",
     *     mappedBy="permissions"
     * )
     */
    protected ?Collection $roles = null;

    public function setOperation(?string $operation): self
    {
        $this->operation = $operation;

        return $this;
    }

    public function getOperation(): ?string
    {
        return $this->operation;
    }

    public function setContexts(array $contexts): self
    {
        $this->contexts = $contexts;

        return $this;
    }

    public function getContexts(): array
    {
        return $this->contexts;
    }

    public function setClass(?string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setField(?string $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function getRoles(): Collection
    {
        return $this->roles ?: $this->roles = new ArrayCollection();
    }
}
