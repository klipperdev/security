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

use Klipper\Component\Security\Exception\InvalidArgumentException;
use Klipper\Component\Security\Identity\SecurityIdentityInterface;
use Klipper\Component\Security\Identity\UserSecurityIdentity;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class UserSecurityIdentityTest extends TestCase
{
    public function testDebugInfo(): void
    {
        $sid = new UserSecurityIdentity(MockUserRoleable::class, 'user.test');

        static::assertSame('UserSecurityIdentity(user.test)', (string) $sid);
    }

    public function testTypeAndIdentifier(): void
    {
        $identity = new UserSecurityIdentity(MockUserRoleable::class, 'identifier');

        static::assertSame(MockUserRoleable::class, $identity->getType());
        static::assertSame('identifier', $identity->getIdentifier());
    }

    public function getIdentities(): array
    {
        $id3 = $this->getMockBuilder(SecurityIdentityInterface::class)->getMock();
        $id3->expects(static::any())->method('getType')->willReturn(MockUserRoleable::class);
        $id3->expects(static::any())->method('getIdentifier')->willReturn('identifier');

        return [
            [new UserSecurityIdentity(MockUserRoleable::class, 'identifier'), true],
            [new UserSecurityIdentity(MockUserRoleable::class, 'other'), false],
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
        $identity = new UserSecurityIdentity(MockUserRoleable::class, 'identifier');

        static::assertSame($result, $identity->equals($value));
    }

    public function testFromAccount(): void
    {
        /** @var MockObject|UserInterface $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $user->expects(static::once())
            ->method('getUsername')
            ->willReturn('user.test')
        ;

        $sid = UserSecurityIdentity::fromAccount($user);

        static::assertSame(\get_class($user), $sid->getType());
        static::assertSame('user.test', $sid->getIdentifier());
    }

    public function testFormToken(): void
    {
        /** @var MockObject|UserInterface $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $user->expects(static::once())
            ->method('getUsername')
            ->willReturn('user.test')
        ;

        /** @var MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects(static::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $sid = UserSecurityIdentity::fromToken($token);

        static::assertSame(\get_class($user), $sid->getType());
        static::assertSame('user.test', $sid->getIdentifier());
    }

    public function testFormTokenWithInvalidInterface(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The user class must implement "Klipper\\Component\\Security\\Model\\UserInterface"');

        /** @var MockObject|\Symfony\Component\Security\Core\User\UserInterface $user */
        $user = $this->getMockBuilder(\Symfony\Component\Security\Core\User\UserInterface::class)->getMock();

        /** @var MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects(static::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        UserSecurityIdentity::fromToken($token);
    }
}
