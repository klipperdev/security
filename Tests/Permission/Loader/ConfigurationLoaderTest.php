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

use Klipper\Component\Security\Permission\Loader\ConfigurationLoader;
use Klipper\Component\Security\Permission\PermissionConfigInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class ConfigurationLoaderTest extends TestCase
{
    public function testSupports(): void
    {
        $loader = new ConfigurationLoader();

        static::assertTrue($loader->supports('.', 'config'));
        static::assertTrue($loader->supports('', 'config'));
        static::assertTrue($loader->supports(null, 'config'));
        static::assertFalse($loader->supports('.', 'foo'));
        static::assertFalse($loader->supports(new \stdClass()));
    }

    /**
     * @throws
     */
    public function testLoad(): void
    {
        $config = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();
        $loader = new ConfigurationLoader([$config]);

        $configs = $loader->load('.', 'config');

        static::assertCount(1, $configs);
        static::assertSame($config, current($configs->all()));
    }
}
