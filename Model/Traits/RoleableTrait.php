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
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\Model\UserInterface;

/**
 * Trait of roleable model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait RoleableTrait
{
    /**
     * @var string[]
     *
     * @ORM\Column(type="json")
     */
    protected $roles = [];

    /**
     * {@inheritdoc}
     */
    public function hasRole(string $role): bool
    {
        return \in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function setRoles(array $roles): self
    {
        $this->roles = [];

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addRole(string $role): self
    {
        $role = strtoupper($role);

        if (!\in_array($role, $this->roles, true) && !\in_array($role, ['ROLE_USER', 'ROLE_ORGANIZATION_USER'], true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeRole(string $role): self
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // we need to make sure to have at least one role
        if ($this instanceof UserInterface && !\in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }

        if ($this instanceof OrganizationUserInterface && !\in_array('ROLE_ORGANIZATION_USER', $roles, true)) {
            $roles[] = 'ROLE_ORGANIZATION_USER';
        }

        return $roles;
    }
}
