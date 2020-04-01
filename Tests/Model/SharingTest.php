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

use Klipper\Component\Security\Tests\Fixtures\Model\MockObject;
use Klipper\Component\Security\Tests\Fixtures\Model\MockPermission;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRole;
use Klipper\Component\Security\Tests\Fixtures\Model\MockSharing;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SharingTest extends TestCase
{
    public function testModel(): void
    {
        $startDate = new \DateTime('now');
        $endDate = new \DateTime('now + 1 day');

        $sharing = new MockSharing();
        $sharing->setSubjectClass(MockObject::class);
        $sharing->setSubjectId(42);
        $sharing->setIdentityClass(MockRole::class);
        $sharing->setIdentityName(23);
        $sharing->setEnabled(true);
        $sharing->setStartedAt($startDate);
        $sharing->setEndedAt($endDate);

        static::assertNull($sharing->getId());
        static::assertSame(MockObject::class, $sharing->getSubjectClass());
        static::assertSame('42', $sharing->getSubjectId());
        static::assertSame(MockRole::class, $sharing->getIdentityClass());
        static::assertSame('23', $sharing->getIdentityName());
        static::assertTrue($sharing->isEnabled());
        static::assertSame($startDate, $sharing->getStartedAt());
        static::assertSame($endDate, $sharing->getEndedAt());
        static::assertCount(0, $sharing->getRoles());

        $perm = new MockPermission();
        static::assertFalse($sharing->hasPermission($perm));

        $sharing->addPermission($perm);
        static::assertTrue($sharing->hasPermission($perm));

        $sharing->removePermission($perm);
        static::assertFalse($sharing->hasPermission($perm));
    }
}
