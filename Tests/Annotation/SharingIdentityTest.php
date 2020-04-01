<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Annotation;

use Klipper\Component\Security\Annotation\SharingIdentity;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SharingIdentityTest extends TestCase
{
    public function testConstructor(): void
    {
        $config = new SharingIdentity([
            'alias' => 'foo',
            'roleable' => true,
            'permissible' => true,
        ]);

        static::assertSame('foo', $config->getAlias());
        static::assertTrue($config->getRoleable());
        static::assertTrue($config->getPermissible());
    }
}
