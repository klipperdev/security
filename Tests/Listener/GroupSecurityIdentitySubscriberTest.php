<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Listener;

use Klipper\Component\Security\Event\AddSecurityIdentityEvent;
use Klipper\Component\Security\Listener\GroupSecurityIdentitySubscriber;
use Klipper\Component\Security\Tests\Fixtures\Model\MockUserGroupable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class GroupSecurityIdentitySubscriberTest extends TestCase
{
    public function testAddGroupSecurityIdentitiesWithException(): void
    {
        $listener = new GroupSecurityIdentitySubscriber();
        static::assertCount(1, GroupSecurityIdentitySubscriber::getSubscribedEvents());

        /** @var MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $sids = [];

        $event = new AddSecurityIdentityEvent($token, $sids);

        $listener->addGroupSecurityIdentities($event);
    }

    public function testAddGroupSecurityIdentities(): void
    {
        $listener = new GroupSecurityIdentitySubscriber();
        static::assertCount(1, GroupSecurityIdentitySubscriber::getSubscribedEvents());

        /** @var MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $sids = [];

        $user = new MockUserGroupable();

        $token->expects(static::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $event = new AddSecurityIdentityEvent($token, $sids);

        $listener->addGroupSecurityIdentities($event);
    }
}
