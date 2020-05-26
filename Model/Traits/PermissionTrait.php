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

    /**
     * {@inheritdoc}
     */
    public function setOperation(?string $operation): self
    {
        $this->operation = $operation;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperation(): ?string
    {
        return $this->operation;
    }

    /**
     * {@inheritdoc}
     */
    public function setContexts(array $contexts): self
    {
        $this->contexts = $contexts;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContexts(): array
    {
        return $this->contexts;
    }

    /**
     * {@inheritdoc}
     */
    public function setClass(?string $class): self
    {
        $this->class = $class;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function setField(?string $field): self
    {
        $this->field = $field;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): Collection
    {
        return $this->roles ?: $this->roles = new ArrayCollection();
    }
}
