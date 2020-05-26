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

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait for role model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait RoleTrait
{
    use PermissionsTrait;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected ?string $name = null;

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return (string) $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(?string $name = null): self
    {
        $this->name = $name;

        return $this;
    }
}
