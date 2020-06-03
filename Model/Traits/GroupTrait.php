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
 * Trait for group model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait GroupTrait
{
    use RoleableTrait;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected ?string $name = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
