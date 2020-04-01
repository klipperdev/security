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

use Klipper\Component\Security\Event\CheckPermissionEvent;
use Klipper\Component\Security\Identity\RoleSecurityIdentity;
use Klipper\Component\Security\Identity\SecurityIdentityInterface;
use Klipper\Component\Security\Identity\SubjectIdentityInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRole;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class CheckPermissionEventTest extends TestCase
{
    public function testEvent(): void
    {
        $sids = [
            $this->getMockBuilder(SecurityIdentityInterface::class)->getMock(),
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $permissionMap = [
            '_global' => [
                'test' => true,
            ],
        ];
        $operation = 'test';
        /** @var SubjectIdentityInterface $subject */
        $subject = $this->getMockBuilder(SubjectIdentityInterface::class)->getMock();
        $field = 'name';

        $event = new CheckPermissionEvent($sids, $permissionMap, $operation, $subject, $field);

        static::assertSame($sids, $event->getSecurityIdentities());
        static::assertSame($permissionMap, $event->getPermissionMap());
        static::assertSame($operation, $event->getOperation());
        static::assertSame($subject, $event->getSubject());
        static::assertSame($field, $event->getField());
        static::assertNull($event->isGranted());

        $event->setGranted(true);

        static::assertTrue($event->isGranted());
    }
}
