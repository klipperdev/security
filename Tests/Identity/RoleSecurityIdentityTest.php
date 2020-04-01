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

use Klipper\Component\Security\Identity\RoleSecurityIdentity;
use Klipper\Component\Security\Identity\SecurityIdentityInterface;
use Klipper\Component\Security\Model\RoleInterface;
use Klipper\Component\Security\Model\Traits\RoleableInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRole;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class RoleSecurityIdentityTest extends TestCase
{
    public function testDebugInfo(): void
    {
        $sid = new RoleSecurityIdentity(MockRole::class, 'ROLE_TEST');

        static::assertSame('RoleSecurityIdentity(ROLE_TEST)', (string) $sid);
    }

    public function testTypeAndIdentifier(): void
    {
        $identity = new RoleSecurityIdentity(MockRole::class, 'identifier');

        static::assertSame(MockRole::class, $identity->getType());
        static::assertSame('identifier', $identity->getIdentifier());
    }

    public function getIdentities(): array
    {
        $id3 = $this->getMockBuilder(SecurityIdentityInterface::class)->getMock();
        $id3->expects(static::any())->method('getType')->willReturn(MockRole::class);
        $id3->expects(static::any())->method('getIdentifier')->willReturn('identifier');

        return [
            [new RoleSecurityIdentity(MockRole::class, 'identifier'), true],
            [new RoleSecurityIdentity(MockRole::class, 'other'), false],
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
        $identity = new RoleSecurityIdentity(MockRole::class, 'identifier');

        static::assertSame($result, $identity->equals($value));
    }

    public function testFromAccount(): void
    {
        /** @var MockObject|RoleInterface $role */
        $role = $this->getMockBuilder(RoleInterface::class)->getMock();
        $role->expects(static::once())
            ->method('getName')
            ->willReturn('ROLE_TEST')
        ;

        $sid = RoleSecurityIdentity::fromAccount($role);

        static::assertInstanceOf(RoleSecurityIdentity::class, $sid);
        static::assertSame(\get_class($role), $sid->getType());
        static::assertSame('ROLE_TEST', $sid->getIdentifier());
    }

    public function testFormToken(): void
    {
        /** @var MockObject|RoleInterface $role */
        $role = $this->getMockBuilder(RoleInterface::class)->getMock();
        $role->expects(static::once())
            ->method('getName')
            ->willReturn('ROLE_TEST')
        ;

        /** @var MockObject|RoleableInterface $user */
        $user = $this->getMockBuilder(RoleableInterface::class)->getMock();
        $user->expects(static::once())
            ->method('getRoles')
            ->willReturn([$role])
        ;

        /** @var MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects(static::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $sids = RoleSecurityIdentity::fromToken($token);

        static::assertCount(1, $sids);
        static::assertInstanceOf(RoleSecurityIdentity::class, $sids[0]);
        static::assertSame(\get_class($role), $sids[0]->getType());
        static::assertSame('ROLE_TEST', $sids[0]->getIdentifier());
    }

    public function testFormTokenWithInvalidInterface(): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The user class must implement "Klipper\\Component\\Security\\Model\\Traits\\RoleableInterface"');

        /** @var MockObject|\Symfony\Component\Security\Core\User\UserInterface $user */
        $user = $this->getMockBuilder(\Symfony\Component\Security\Core\User\UserInterface::class)->getMock();

        /** @var MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects(static::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        RoleSecurityIdentity::fromToken($token);
    }
}
