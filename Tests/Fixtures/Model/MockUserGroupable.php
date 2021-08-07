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
use Klipper\Component\Security\Model\Traits\UserTrait;
use Klipper\Component\Security\Model\UserInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MockUserGroupable implements UserInterface, EditGroupableInterface
{
    use EditGroupableTrait;
    use RoleableTrait;
    use UserTrait;

    public function __construct($mockGroups = true)
    {
        if ($mockGroups) {
            $this->addGroup(new MockGroup('GROUP_TEST'));
        }
    }

    public function getId()
    {
        return null;
    }

    public function getPassword(): ?string
    {
        return 'password';
    }

    public function getSalt(): ?string
    {
        return 'salt';
    }

    public function getUsername(): string
    {
        return 'user.test';
    }

    public function getUserIdentifier(): string
    {
        return 'user.test';
    }

    public function eraseCredentials(): void
    {
        // do nothing
    }

    public function getRoles(): array
    {
        return ['ROLE_TEST'];
    }
}
