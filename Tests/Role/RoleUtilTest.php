<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Role;

use Klipper\Component\Security\Role\RoleUtil;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRole;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class RoleUtilTest extends TestCase
{
    public function testFormatNames(): void
    {
        $value = [
            new MockRole('ROLE_USER', 23),
            new MockRole('ROLE_TEST', 32),
        ];
        $expected = [
            'ROLE_USER',
            'ROLE_TEST',
        ];

        static::assertEquals($expected, RoleUtil::formatNames($value));
    }

    public function testFormatName(): void
    {
        $value = new MockRole('ROLE_TEST');
        $expected = 'ROLE_TEST';

        static::assertEquals($expected, RoleUtil::formatName($value));
    }
}
