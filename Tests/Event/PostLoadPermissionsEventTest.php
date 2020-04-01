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

use Klipper\Component\Security\Event\PostLoadPermissionsEvent;
use Klipper\Component\Security\Identity\RoleSecurityIdentity;
use Klipper\Component\Security\Identity\SecurityIdentityInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRole;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class PostLoadPermissionsEventTest extends TestCase
{
    public function testEvent(): void
    {
        $sids = [
            $this->getMockBuilder(SecurityIdentityInterface::class)->getMock(),
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $roles = [
            'ROLE_USER',
        ];
        $permissionMap = [];

        $event = new PostLoadPermissionsEvent($sids, $roles, $permissionMap);

        static::assertSame($sids, $event->getSecurityIdentities());
        static::assertSame($roles, $event->getRoles());
        static::assertSame($permissionMap, $event->getPermissionMap());

        $permissionMap2 = [
            '_global' => [
                'test' => true,
            ],
        ];
        $event->setPermissionMap($permissionMap2);

        static::assertSame($permissionMap2, $event->getPermissionMap());
    }
}
