<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Event;

use Klipper\Component\Security\Event\PostReachableRoleEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class PostReachableRoleEventTest extends TestCase
{
    public function testEvent(): void
    {
        $roles = [
            'ROLE_FOO',
            'ROLE_BAR',
        ];

        $event = new PostReachableRoleEvent($roles);
        static::assertSame($roles, $event->getReachableRoleNames());
        static::assertTrue($event->isPermissionEnabled());

        $roles[] = 'ROLE_BAZ';
        $event->setReachableRoleNames($roles);
        static::assertSame($roles, $event->getReachableRoleNames());
    }
}
