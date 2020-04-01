<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Sharing\Loader;

use Klipper\Component\Config\ArrayResource;
use Klipper\Component\Security\Sharing\Loader\IdentityArrayResourceLoader;
use Klipper\Component\Security\Sharing\SharingIdentityConfigCollection;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class IdentityArrayResourceLoaderTest extends TestCase
{
    public function testSupports(): void
    {
        $loader = new IdentityArrayResourceLoader();

        static::assertTrue($loader->supports(new ArrayResource()));
        static::assertTrue($loader->supports(new ArrayResource(), 'foo'));
        static::assertFalse($loader->supports(new \stdClass()));
    }

    /**
     * @throws
     */
    public function testLoad(): void
    {
        $resource = new ArrayResource();
        $loader = new IdentityArrayResourceLoader();

        $configs = $loader->load($resource);

        static::assertInstanceOf(SharingIdentityConfigCollection::class, $configs);
        static::assertCount(0, $configs);
    }
}
