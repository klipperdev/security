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

use Klipper\Component\Security\Model\PermissionChecking;
use Klipper\Component\Security\Tests\Fixtures\Model\MockPermission;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class PermissionCheckingTest extends TestCase
{
    public function testModel(): void
    {
        $perm = new MockPermission();
        $permChecking = new PermissionChecking($perm, true, true);

        static::assertSame($perm, $permChecking->getPermission());
        static::assertTrue($permChecking->isGranted());
        static::assertTrue($permChecking->isLocked());
    }
}
