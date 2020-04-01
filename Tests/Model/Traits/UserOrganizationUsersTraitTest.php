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

use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockUserOrganizationUsers;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class UserOrganizationUsersTraitTest extends TestCase
{
    public function testModel(): void
    {
        $user = new MockUserOrganizationUsers();

        static::assertCount(0, $user->getUserOrganizations());
        static::assertCount(0, $user->getUserOrganizationNames());
        static::assertFalse($user->hasUserOrganization('foo'));
        static::assertNull($user->getUserOrganization('foo'));

        /** @var MockObject|OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        $org->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn('foo')
        ;
        $org->expects(static::once())
            ->method('isUserOrganization')
            ->willReturn(false)
        ;

        /** @var MockObject|OrganizationUserInterface $orgUser */
        $orgUser = $this->getMockBuilder(OrganizationUserInterface::class)->getMock();
        $orgUser->expects(static::atLeastOnce())
            ->method('getOrganization')
            ->willReturn($org)
        ;

        $user->addUserOrganization($orgUser);

        static::assertCount(1, $user->getUserOrganizations());
        static::assertCount(1, $user->getUserOrganizationNames());
        static::assertTrue($user->hasUserOrganization('foo'));
        static::assertSame($orgUser, $user->getUserOrganization('foo'));

        $user->removeUserOrganization($orgUser);

        static::assertCount(0, $user->getUserOrganizations());
        static::assertCount(0, $user->getUserOrganizationNames());
        static::assertFalse($user->hasUserOrganization('foo'));
        static::assertNull($user->getUserOrganization('foo'));
    }
}
