<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Model\Traits;

use Klipper\Component\Security\Tests\Fixtures\Model\MockPermissionSharing;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class PermissionSharingEntryTraitTest extends TestCase
{
    public function testModel(): void
    {
        $permission = new MockPermissionSharing();
        static::assertCount(0, $permission->getSharingEntries());
    }
}
