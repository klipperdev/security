<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Sharing;

use Klipper\Component\Security\Sharing\SharingFactoryCacheWarmer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SharingFactoryCacheWarmerTest extends TestCase
{
    public function testWarmUp(): void
    {
        $cacheLoader = $this->getMockBuilder(WarmableInterface::class)->getMock();
        $cacheLoader->expects(static::once())
            ->method('warmUp')
            ->with('cache_dir')
        ;

        /** @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $container->expects(static::once())
            ->method('get')
            ->with('klipper_security.sharing_factory')
            ->willReturn($cacheLoader)
        ;

        $warmer = new SharingFactoryCacheWarmer($container);
        static::assertTrue($warmer->isOptional());

        $warmer->warmUp('cache_dir');
    }
}
