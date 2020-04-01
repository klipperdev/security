<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Annotation;

use Klipper\Component\Config\Annotation\AbstractAnnotation;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @Annotation
 * @Target({"CLASS"})
 */
class SharingIdentity extends AbstractAnnotation
{
    /**
     * @var null|string
     */
    protected $alias;

    /**
     * @var null|bool
     */
    protected $roleable;

    /**
     * @var null|bool
     */
    protected $permissible;

    /**
     * @return null|string
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @param null|string $alias
     */
    public function setAlias(?string $alias): void
    {
        $this->alias = $alias;
    }

    /**
     * @return null|bool
     */
    public function getRoleable(): ?bool
    {
        return $this->roleable;
    }

    /**
     * @param null|bool $roleable
     */
    public function setRoleable(?bool $roleable): void
    {
        $this->roleable = $roleable;
    }

    /**
     * @return null|bool
     */
    public function getPermissible(): ?bool
    {
        return $this->permissible;
    }

    /**
     * @param null|bool $permissible
     */
    public function setPermissible(?bool $permissible): void
    {
        $this->permissible = $permissible;
    }
}
