<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Identity;

use Klipper\Component\Security\Identity\GroupSecurityIdentity;
use Klipper\Component\Security\Identity\OrganizationSecurityIdentity;
use Klipper\Component\Security\Identity\RoleSecurityIdentity;
use Klipper\Component\Security\Identity\SecurityIdentityInterface;
use Klipper\Component\Security\Model\GroupInterface;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockOrganization;
use Klipper\Component\Security\Tests\Fixtures\Model\MockOrganizationUserRoleableGroupable;
use Klipper\Component\Security\Tests\Fixtures\Model\MockUserOrganizationUsersGroupable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class OrganizationSecurityIdentityTest extends TestCase
{
    public function testDebugInfo(): void
    {
        $sid = new OrganizationSecurityIdentity(MockOrganization::class, 'foo');

        static::assertSame('OrganizationSecurityIdentity(foo)', (string) $sid);
    }

    public function testTypeAndIdentifier(): void
    {
        $identity = new OrganizationSecurityIdentity(MockOrganization::class, 'identifier');

        static::assertSame(MockOrganization::class, $identity->getType());
        static::assertSame('identifier', $identity->getIdentifier());
    }

    public function getIdentities(): array
    {
        $id3 = $this->getMockBuilder(SecurityIdentityInterface::class)->getMock();
        $id3->expects(static::any())->method('getType')->willReturn(MockOrganization::class);
        $id3->expects(static::any())->method('getIdentifier')->willReturn('identifier');

        return [
            [new OrganizationSecurityIdentity(MockOrganization::class, 'identifier'), true],
            [new OrganizationSecurityIdentity(MockOrganization::class, 'other'), false],
            [$id3, false],
        ];
    }

    /**
     * @dataProvider getIdentities
     *
     * @param mixed $value  The value
     * @param bool  $result The expected result
     */
    public function testEquals($value, bool $result): void
    {
        $identity = new OrganizationSecurityIdentity(MockOrganization::class, 'identifier');

        static::assertSame($result, $identity->equals($value));
    }

    public function testFromAccount(): void
    {
        /** @var MockObject|OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        $org->expects(static::once())
            ->method('getName')
            ->willReturn('foo')
        ;

        $sid = OrganizationSecurityIdentity::fromAccount($org);

        static::assertInstanceOf(OrganizationSecurityIdentity::class, $sid);
        static::assertSame(\get_class($org), $sid->getType());
        static::assertSame('foo', $sid->getIdentifier());
    }

    public function testFormTokenWithoutOrganizationalContext(): void
    {
        $user = new MockUserOrganizationUsersGroupable();
        $org = new MockOrganization('foo');
        $orgUser = new MockOrganizationUserRoleableGroupable($org, $user);

        $org->addRole('ROLE_ORG_TEST');

        /** @var GroupInterface|MockObject $group */
        $group = $this->getMockBuilder(GroupInterface::class)->getMock();
        $group->expects(static::once())
            ->method('getName')
            ->willReturn('GROUP_ORG_USER_TEST')
        ;

        $orgUser->addGroup($group);
        $orgUser->addRole('ROLE_ORG_USER_TEST');

        $user->addUserOrganization($orgUser);

        /** @var MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects(static::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        /** @var MockObject|RoleHierarchyInterface $roleHierarchy */
        $roleHierarchy = $this->getMockBuilder(RoleHierarchy::class)->disableOriginalConstructor()->getMock();
        $roleHierarchy->expects(static::once())
            ->method('getReachableRoleNames')
            ->willReturnCallback(static function ($value) {
                return $value;
            })
        ;

        $sids = OrganizationSecurityIdentity::fromToken($token, null, $roleHierarchy);

        static::assertCount(5, $sids);
        static::assertInstanceOf(OrganizationSecurityIdentity::class, $sids[0]);
        static::assertSame('foo', $sids[0]->getIdentifier());
        static::assertInstanceOf(GroupSecurityIdentity::class, $sids[1]);
        static::assertSame('GROUP_ORG_USER_TEST__foo', $sids[1]->getIdentifier());
        static::assertInstanceOf(RoleSecurityIdentity::class, $sids[2]);
        static::assertSame('ROLE_ORG_USER_TEST__foo', $sids[2]->getIdentifier());
        static::assertInstanceOf(RoleSecurityIdentity::class, $sids[3]);
        static::assertSame('ROLE_ORGANIZATION_USER__foo', $sids[3]->getIdentifier());
        static::assertInstanceOf(RoleSecurityIdentity::class, $sids[4]);
        static::assertSame('ROLE_ORG_TEST__foo', $sids[4]->getIdentifier());
    }

    public function testFormTokenWithOrganizationalContext(): void
    {
        $user = new MockUserOrganizationUsersGroupable();
        $org = new MockOrganization('foo');
        $orgUser = new MockOrganizationUserRoleableGroupable($org, $user);

        $org->addRole('ROLE_ORG_TEST');

        /** @var GroupInterface|MockObject $group */
        $group = $this->getMockBuilder(GroupInterface::class)->getMock();
        $group->expects(static::once())
            ->method('getName')
            ->willReturn('GROUP_ORG_USER_TEST')
        ;

        $orgUser->addGroup($group);
        $orgUser->addRole('ROLE_ORG_USER_TEST');

        $user->addUserOrganization($orgUser);

        /** @var MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects(static::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        /** @var MockObject|OrganizationalContextInterface $context */
        $context = $this->getMockBuilder(OrganizationalContextInterface::class)->getMock();
        $context->expects(static::once())
            ->method('getCurrentOrganization')
            ->willReturn($org)
        ;
        $context->expects(static::once())
            ->method('getCurrentOrganizationUser')
            ->willReturn($orgUser)
        ;

        /** @var MockObject|RoleHierarchyInterface $roleHierarchy */
        $roleHierarchy = $this->getMockBuilder(RoleHierarchy::class)->disableOriginalConstructor()->getMock();
        $roleHierarchy->expects(static::once())
            ->method('getReachableRoleNames')
            ->willReturnCallback(static function ($value) {
                return $value;
            })
        ;

        $sids = OrganizationSecurityIdentity::fromToken($token, $context, $roleHierarchy);

        static::assertCount(5, $sids);
        static::assertInstanceOf(OrganizationSecurityIdentity::class, $sids[0]);
        static::assertSame('foo', $sids[0]->getIdentifier());
        static::assertInstanceOf(GroupSecurityIdentity::class, $sids[1]);
        static::assertSame('GROUP_ORG_USER_TEST__foo', $sids[1]->getIdentifier());
        static::assertInstanceOf(RoleSecurityIdentity::class, $sids[2]);
        static::assertSame('ROLE_ORG_USER_TEST__foo', $sids[2]->getIdentifier());
        static::assertInstanceOf(RoleSecurityIdentity::class, $sids[3]);
        static::assertSame('ROLE_ORGANIZATION_USER__foo', $sids[3]->getIdentifier());
        static::assertInstanceOf(RoleSecurityIdentity::class, $sids[4]);
        static::assertSame('ROLE_ORG_TEST__foo', $sids[4]->getIdentifier());
    }

    public function testFormTokenWithUserOrganizationalContext(): void
    {
        $user = new MockUserOrganizationUsersGroupable();
        $org = new MockOrganization($user->getUserIdentifier());
        $org->setUser($user);

        $org->addRole('ROLE_ORG_TEST');

        /** @var MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects(static::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        /** @var MockObject|OrganizationalContextInterface $context */
        $context = $this->getMockBuilder(OrganizationalContextInterface::class)->getMock();
        $context->expects(static::once())
            ->method('getCurrentOrganization')
            ->willReturn($org)
        ;
        $context->expects(static::once())
            ->method('getCurrentOrganizationUser')
            ->willReturn(null)
        ;

        /** @var MockObject|RoleHierarchyInterface $roleHierarchy */
        $roleHierarchy = $this->getMockBuilder(RoleHierarchy::class)->disableOriginalConstructor()->getMock();
        $roleHierarchy->expects(static::once())
            ->method('getReachableRoleNames')
            ->willReturnCallback(static function ($value) {
                return $value;
            })
        ;

        $sids = OrganizationSecurityIdentity::fromToken($token, $context, $roleHierarchy);

        static::assertCount(2, $sids);
        static::assertInstanceOf(OrganizationSecurityIdentity::class, $sids[0]);
        static::assertSame('user.test', $sids[0]->getIdentifier());
        static::assertInstanceOf(RoleSecurityIdentity::class, $sids[1]);
        static::assertSame('ROLE_ORG_TEST__user.test', $sids[1]->getIdentifier());
    }

    public function testFormTokenWithInvalidInterface(): void
    {
        /** @var MockObject|UserInterface $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();

        /** @var MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects(static::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $sids = OrganizationSecurityIdentity::fromToken($token);

        static::assertCount(0, $sids);
    }
}
