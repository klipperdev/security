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

use Klipper\Component\Security\Model\GroupInterface;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockOrganization;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRole;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class OrganizationTest extends TestCase
{
    public function testModel(): void
    {
        $org = new MockOrganization('FOO');

        static::assertSame(23, $org->getId());
        static::assertSame('FOO', $org->getName());
        static::assertNull($org->getUser());
        static::assertFalse($org->isUserOrganization());
        static::assertCount(0, $org->getOrganizationRoles());
        static::assertCount(0, $org->getOrganizationRoleNames());
        static::assertFalse($org->hasOrganizationRole('ROLE_ADMIN'));
        static::assertCount(0, $org->getOrganizationGroups());
        static::assertCount(0, $org->getOrganizationGroupNames());
        static::assertFalse($org->hasOrganizationGroup('GROUP_DEFAULT'));
        static::assertCount(0, $org->getOrganizationUsers());
        static::assertCount(0, $org->getOrganizationUserNames());
        static::assertFalse($org->hasOrganizationUser('user.test'));
        static::assertSame('FOO', (string) $org);
    }

    public function testModelName(): void
    {
        $org = new MockOrganization('FOO');

        static::assertSame('FOO', $org->getName());
        $org->setName('BAR');
        static::assertSame('BAR', $org->getName());
    }

    public function testModelUser(): void
    {
        /** @var UserInterface $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $org = new MockOrganization('FOO');

        static::assertNull($org->getUser());
        static::assertFalse($org->isUserOrganization());

        $org->setUser($user);
        static::assertSame($user, $org->getUser());
        static::assertTrue($org->isUserOrganization());
    }

    public function testModelRoles(): void
    {
        $role = new MockRole('ROLE_ADMIN');
        $org = new MockOrganization('FOO');

        static::assertCount(0, $org->getOrganizationRoles());
        static::assertCount(0, $org->getOrganizationRoleNames());
        static::assertFalse($org->hasOrganizationRole('ROLE_ADMIN'));

        $org->addOrganizationRole($role);

        static::assertCount(1, $org->getOrganizationRoles());
        static::assertCount(1, $org->getOrganizationRoleNames());
        static::assertTrue($org->hasOrganizationRole('ROLE_ADMIN'));
        static::assertSame('ROLE_ADMIN', current($org->getOrganizationRoleNames()));

        $org->removeOrganizationRole($role);

        static::assertCount(0, $org->getOrganizationRoles());
        static::assertCount(0, $org->getOrganizationRoleNames());
        static::assertFalse($org->hasOrganizationRole('ROLE_ADMIN'));
    }

    public function testModelGroups(): void
    {
        /** @var GroupInterface|MockObject $group */
        $group = $this->getMockBuilder(GroupInterface::class)->getMock();
        $group->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn('GROUP_DEFAULT')
        ;

        $org = new MockOrganization('FOO');

        static::assertCount(0, $org->getOrganizationGroups());
        static::assertCount(0, $org->getOrganizationGroupNames());
        static::assertFalse($org->hasOrganizationRole('GROUP_DEFAULT'));

        $org->addOrganizationGroup($group);

        static::assertCount(1, $org->getOrganizationGroups());
        static::assertCount(1, $org->getOrganizationGroupNames());
        static::assertTrue($org->hasOrganizationGroup('GROUP_DEFAULT'));
        static::assertSame('GROUP_DEFAULT', current($org->getOrganizationGroupNames()));

        $org->removeOrganizationGroup($group);

        static::assertCount(0, $org->getOrganizationGroups());
        static::assertCount(0, $org->getOrganizationGroupNames());
        static::assertFalse($org->hasOrganizationGroup('GROUP_DEFAULT'));
    }

    public function testModelUsers(): void
    {
        /** @var MockObject|UserInterface $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $user->expects(static::atLeastOnce())
            ->method('getUserIdentifier')
            ->willReturn('user.test')
        ;

        /** @var MockObject|OrganizationUserInterface $orgUser */
        $orgUser = $this->getMockBuilder(OrganizationUserInterface::class)->getMock();
        $orgUser->expects(static::atLeastOnce())
            ->method('getUser')
            ->willReturn($user)
        ;

        $org = new MockOrganization('FOO');

        static::assertCount(0, $org->getOrganizationUsers());
        static::assertCount(0, $org->getOrganizationUserNames());
        static::assertFalse($org->hasOrganizationUser($user->getUserIdentifier()));

        $org->addOrganizationUser($orgUser);

        static::assertCount(1, $org->getOrganizationUsers());
        static::assertCount(1, $org->getOrganizationUserNames());
        static::assertTrue($org->hasOrganizationUser($user->getUserIdentifier()));
        static::assertSame($user->getUserIdentifier(), current($org->getOrganizationUserNames()));

        $org->removeOrganizationUser($orgUser);

        static::assertCount(0, $org->getOrganizationUsers());
        static::assertCount(0, $org->getOrganizationUserNames());
        static::assertFalse($org->hasOrganizationUser($user->getUserIdentifier()));
    }
}
