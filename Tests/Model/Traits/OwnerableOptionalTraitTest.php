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

use Klipper\Component\Security\Tests\Fixtures\Model\MockObjectOwnerableOptional;
use Klipper\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class OwnerableOptionalTraitTest extends TestCase
{
    public function testModel(): void
    {
        $user = new MockUserRoleable();
        $ownerable = new MockObjectOwnerableOptional('foo');

        static::assertNull($ownerable->getOwner());
        static::assertNull($ownerable->getOwnerId());

        $ownerable->setOwner($user);

        static::assertSame($user, $ownerable->getOwner());
        static::assertSame(50, $ownerable->getOwnerId());
    }
}
