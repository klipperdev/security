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
use Klipper\Component\Security\Identity\SecurityIdentityInterface;
use Klipper\Component\Security\Model\GroupInterface;
use Klipper\Component\Security\Model\Traits\GroupableInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockGroup;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class GroupSecurityIdentityTest extends TestCase
{
    public function testDebugInfo(): void
    {
        $sid = new GroupSecurityIdentity(MockGroup::class, 'GROUP_TEST');

        static::assertSame('GroupSecurityIdentity(GROUP_TEST)', (string) $sid);
    }

    public function testTypeAndIdentifier(): void
    {
        $identity = new GroupSecurityIdentity(MockGroup::class, 'identifier');

        static::assertSame(MockGroup::class, $identity->getType());
        static::assertSame('identifier', $identity->getIdentifier());
    }

    public function getIdentities(): array
    {
        $id3 = $this->getMockBuilder(SecurityIdentityInterface::class)->getMock();
        $id3->expects(static::any())->method('getType')->willReturn(MockGroup::class);
        $id3->expects(static::any())->method('getIdentifier')->willReturn('identifier');

        return [
            [new GroupSecurityIdentity(MockGroup::class, 'identifier'), true],
            [new GroupSecurityIdentity(MockGroup::class, 'other'), false],
            [$id3, false],
        ];
    }

    /**
     * @dataProvider getIdentities
     *
     * @param mixed $value  The value
     * @param bool  $result The expected result
     */
    public function testEquals($value, $result): void
    {
        $identity = new GroupSecurityIdentity(MockGroup::class, 'identifier');

        static::assertSame($result, $identity->equals($value));
    }

    public function testFromAccount(): void
    {
        /** @var GroupInterface|MockObject $group */
        $group = $this->getMockBuilder(GroupInterface::class)->getMock();
        $group->expects(static::once())
            ->method('getName')
            ->willReturn('GROUP_TEST')
        ;

        $sid = GroupSecurityIdentity::fromAccount($group);

        static::assertInstanceOf(GroupSecurityIdentity::class, $sid);
        static::assertSame(\get_class($group), $sid->getType());
        static::assertSame('GROUP_TEST', $sid->getIdentifier());
    }

    public function testFormToken(): void
    {
        /** @var GroupInterface|MockObject $group */
        $group = $this->getMockBuilder(GroupInterface::class)->getMock();
        $group->expects(static::once())
            ->method('getName')
            ->willReturn('GROUP_TEST')
        ;

        /** @var GroupableInterface|MockObject $user */
        $user = $this->getMockBuilder(GroupableInterface::class)->getMock();
        $user->expects(static::once())
            ->method('getGroups')
            ->willReturn([$group])
        ;

        /** @var MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects(static::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $sids = GroupSecurityIdentity::fromToken($token);

        static::assertCount(1, $sids);
        static::assertInstanceOf(GroupSecurityIdentity::class, $sids[0]);
        static::assertSame(\get_class($group), $sids[0]->getType());
        static::assertSame('GROUP_TEST', $sids[0]->getIdentifier());
    }

    public function testFormTokenWithInvalidInterface(): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The user class must implement "Klipper\\Component\\Security\\Model\\Traits\\GroupableInterface"');

        /** @var MockObject|UserInterface $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();

        /** @var MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects(static::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        GroupSecurityIdentity::fromToken($token);
    }
}
