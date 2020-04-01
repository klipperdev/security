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

use Klipper\Component\Security\Model\Traits\EditGroupableInterface;
use Klipper\Component\Security\Model\Traits\EditGroupableTrait;
use Klipper\Component\Security\Model\Traits\RoleableTrait;
use Klipper\Component\Security\Model\UserInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MockUserGroupable implements UserInterface, EditGroupableInterface
{
    use RoleableTrait;
    use EditGroupableTrait;

    public function __construct($mockGroups = true)
    {
        if ($mockGroups) {
            $this->addGroup(new MockGroup('GROUP_TEST'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword(): string
    {
        return 'password';
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt(): string
    {
        return 'salt';
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername(): string
    {
        return 'user.test';
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        return ['ROLE_TEST'];
    }
}
