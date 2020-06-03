<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Fixtures\Model;

use Klipper\Component\Security\Model\GroupInterface;
use Klipper\Component\Security\Model\Traits\GroupTrait;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MockGroup implements GroupInterface
{
    use GroupTrait;

    protected ?int $id = null;

    protected array $roles = [];

    /**
     * @param string $name The group name
     * @param int    $id   The id
     */
    public function __construct($name, $id = 23)
    {
        $this->name = $name;
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function hasRole(string $role): bool
    {
        return \in_array($role, $this->roles, true);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): self
    {
        if (!$this->hasRole($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(string $role): self
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }
}
