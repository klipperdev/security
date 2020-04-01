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

use Klipper\Component\Security\Tests\Fixtures\Model\MockGroup;
use Klipper\Component\Security\Tests\Fixtures\Model\MockOrganization;
use Klipper\Component\Security\Tests\Fixtures\Model\MockOrganizationUserRoleableGroupable;
use Klipper\Component\Security\Tests\Fixtures\Model\MockUserGroupable;
use Klipper\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class GroupableTraitTest extends TestCase
{
    public function testUserModel(): void
    {
        $groupable = new MockUserGroupable(false);
        $group = new MockGroup('GROUP_TEST');

        static::assertEmpty($groupable->getGroups());

        $groupable->addGroup($group);

        static::assertFalse($groupable->hasGroup('GROUP_FOO'));
        static::assertTrue($groupable->hasGroup('GROUP_TEST'));
        static::assertEquals(['GROUP_TEST'], $groupable->getGroupNames());
        static::assertEquals([$group], $groupable->getGroups()->toArray());

        $groupable->removeGroup($group);

        static::assertFalse($groupable->hasGroup('GROUP_TEST'));
        static::assertEquals([], $groupable->getGroupNames());
        static::assertEquals([], $groupable->getGroups()->toArray());
    }

    public function testOrganizationUserModel(): void
    {
        $org = new MockOrganization('foo');
        $user = new MockUserRoleable();
        $groupable = new MockOrganizationUserRoleableGroupable($org, $user);
        $group = new MockGroup('GROUP_TEST');

        static::assertEmpty($groupable->getGroups());

        $groupable->addGroup($group);

        static::assertFalse($groupable->hasGroup('GROUP_FOO'));
        static::assertTrue($groupable->hasGroup('GROUP_TEST'));
        static::assertEquals(['GROUP_TEST'], $groupable->getGroupNames());
        static::assertEquals([$group], $groupable->getGroups()->toArray());

        $groupable->removeGroup($group);

        static::assertFalse($groupable->hasGroup('GROUP_TEST'));
        static::assertEquals([], $groupable->getGroupNames());
        static::assertEquals([], $groupable->getGroups()->toArray());
    }
}
