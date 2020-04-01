<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Model;

use Klipper\Component\Security\Tests\Fixtures\Model\MockPermission;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRole;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class RoleTest extends TestCase
{
    public function testModel(): void
    {
        $nameUser = 'ROLE_USER';
        $nameAdmin = 'ROLE_ADMIN';
        $role = new MockRole($nameUser);

        static::assertSame(23, $role->getId());
        static::assertSame($nameUser, $role->getName());

        $role->setName($nameAdmin);
        static::assertSame($nameAdmin, $role->getName());
        static::assertSame($nameAdmin, (string) $role);

        static::assertCount(0, $role->getParents());
        static::assertCount(0, $role->getParentNames());
        static::assertFalse($role->hasParent('PARENT'));

        static::assertCount(0, $role->getChildren());
        static::assertCount(0, $role->getChildrenNames());
        static::assertFalse($role->hasChild('CHILD'));
    }

    public function testModelPermissions(): void
    {
        $role = new MockRole('ROLE_USER');
        $perm = new MockPermission();

        static::assertCount(0, $role->getPermissions());
        static::assertFalse($role->hasPermission($perm));

        $role->addPermission($perm);
        static::assertTrue($role->hasPermission($perm));

        $role->removePermission($perm);
        static::assertFalse($role->hasPermission($perm));
    }

    public function testParent(): void
    {
        $roleUser = new MockRole('ROLE_USER');
        $roleAdmin = new MockRole('ROLE_ADMIN');

        static::assertCount(0, $roleUser->getParents());
        static::assertCount(0, $roleUser->getChildren());
        static::assertCount(0, $roleAdmin->getParents());
        static::assertCount(0, $roleAdmin->getChildren());

        $roleUser->addParent($roleAdmin);

        static::assertCount(1, $roleUser->getParents());
        static::assertCount(0, $roleUser->getChildren());
        static::assertCount(0, $roleAdmin->getParents());
        static::assertCount(1, $roleAdmin->getChildren());

        static::assertSame('ROLE_ADMIN', current($roleUser->getParentNames()));
        static::assertSame('ROLE_USER', current($roleAdmin->getChildrenNames()));

        $roleUser->removeParent($roleAdmin);

        static::assertCount(0, $roleUser->getParents());
        static::assertCount(0, $roleUser->getChildren());
        static::assertCount(0, $roleAdmin->getParents());
        static::assertCount(0, $roleAdmin->getChildren());
    }

    public function testChildren(): void
    {
        $roleUser = new MockRole('ROLE_USER');
        $roleAdmin = new MockRole('ROLE_ADMIN');

        static::assertCount(0, $roleUser->getParents());
        static::assertCount(0, $roleUser->getChildren());
        static::assertCount(0, $roleAdmin->getParents());
        static::assertCount(0, $roleAdmin->getChildren());

        $roleAdmin->addChild($roleUser);

        static::assertCount(0, $roleUser->getParents());
        static::assertCount(0, $roleUser->getChildren());
        static::assertCount(0, $roleAdmin->getParents());
        static::assertCount(1, $roleAdmin->getChildren());

        static::assertSame('ROLE_USER', current($roleAdmin->getChildrenNames()));

        $roleAdmin->removeChild($roleUser);

        static::assertCount(0, $roleUser->getParents());
        static::assertCount(0, $roleUser->getChildren());
        static::assertCount(0, $roleAdmin->getParents());
        static::assertCount(0, $roleAdmin->getChildren());
    }
}
