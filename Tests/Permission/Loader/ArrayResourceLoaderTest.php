<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Permission\Loader;

use Klipper\Component\Config\ArrayResource;
use Klipper\Component\Security\Permission\Loader\ArrayResourceLoader;
use Klipper\Component\Security\Permission\PermissionConfigCollection;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class ArrayResourceLoaderTest extends TestCase
{
    public function testSupports(): void
    {
        $loader = new ArrayResourceLoader();

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
        $loader = new ArrayResourceLoader();

        $configs = $loader->load($resource);

        static::assertInstanceOf(PermissionConfigCollection::class, $configs);
        static::assertCount(0, $configs);
    }
}
