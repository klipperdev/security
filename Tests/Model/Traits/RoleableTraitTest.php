<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Model\Traits;

use Klipper\Component\Security\Tests\Fixtures\Model\MockOrganization;
use Klipper\Component\Security\Tests\Fixtures\Model\MockOrganizationUserRoleableGroupable;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRoleable;
use Klipper\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class RoleableTraitTest extends TestCase
{
    public function testModel(): void
    {
        $roleable = new MockRoleable();

        static::assertFalse($roleable->hasRole('ROLE_TEST'));

        $roleable->setRoles([
            'ROLE_TEST',
            'ROLE_USER',
            'ROLE_ORGANIZATION_USER',
        ]);

        static::assertTrue($roleable->hasRole('ROLE_TEST'));
        static::assertFalse($roleable->hasRole('ROLE_USER')); // Skip the ROLE_USER role
        static::assertFalse($roleable->hasRole('ROLE_ORGANIZATION_USER')); // Skip the ROLE_ORGANIZATION_USER role

        static::assertEquals(['ROLE_TEST'], $roleable->getRoles());

        $roleable->removeRole('ROLE_TEST');
        static::assertFalse($roleable->hasRole('ROLE_TEST'));
    }

    public function testUserModel(): void
    {
        $roleable = new MockUserRoleable();

        static::assertEquals(['ROLE_USER'], $roleable->getRoles());

        $roleable->addRole('ROLE_TEST');

        $validRoles = [
            'ROLE_TEST',
            'ROLE_USER',
        ];
        static::assertEquals($validRoles, $roleable->getRoles());
    }

    public function testOrganizationUserModel(): void
    {
        $org = new MockOrganization('foo');
        $user = new MockUserRoleable();
        $roleable = new MockOrganizationUserRoleableGroupable($org, $user);

        static::assertEquals(['ROLE_ORGANIZATION_USER'], $roleable->getRoles());

        $roleable->addRole('ROLE_TEST');

        $validRoles = [
            'ROLE_TEST',
            'ROLE_ORGANIZATION_USER',
        ];
        static::assertEquals($validRoles, $roleable->getRoles());
    }
}
