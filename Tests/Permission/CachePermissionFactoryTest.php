<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Permission;

use Klipper\Component\Security\Permission\CachePermissionFactory;
use Klipper\Component\Security\Permission\PermissionFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class CachePermissionFactoryTest extends TestCase
{
    /**
     * @var MockObject|PermissionFactoryInterface
     */
    private $factory;

    /**
     * @var ConfigCacheFactoryInterface|MockObject
     */
    private $configCacheFactory;

    private ?string $cacheDir = null;

    protected function setUp(): void
    {
        $this->factory = $this->getMockBuilder(PermissionFactoryInterface::class)->getMock();
        $this->configCacheFactory = $this->getMockBuilder(ConfigCacheFactoryInterface::class)->getMock();
        $this->cacheDir = sys_get_temp_dir().uniqid('/klipper_security_', true);
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $fs->remove($this->cacheDir);

        $this->factory = null;
        $this->configCacheFactory = null;
        $this->cacheDir = null;
    }

    public function testCreateConfigsWithoutCacheDir(): void
    {
        $cacheFactory = new CachePermissionFactory($this->factory, [
            'cache_dir' => null,
        ]);
        $cacheFactory->setConfigCacheFactory($this->configCacheFactory);

        $this->factory->expects(static::once())
            ->method('createConfigurations')
        ;

        $this->configCacheFactory->expects(static::never())
            ->method('cache')
        ;

        $cacheFactory->createConfigurations();
    }

    public function testCreateConfigsWithDebug(): void
    {
        $cacheFactory = new CachePermissionFactory($this->factory, [
            'debug' => true,
        ]);
        $cacheFactory->setConfigCacheFactory($this->configCacheFactory);

        $this->factory->expects(static::once())
            ->method('createConfigurations')
        ;

        $this->configCacheFactory->expects(static::never())
            ->method('cache')
        ;

        $cacheFactory->createConfigurations();
    }

    public function testCreateConfigsWithCacheDir(): void
    {
        $fs = new Filesystem();

        $cacheFileConfigs = $this->cacheDir.'/cache_file_configs.php';
        $fs->dumpFile($cacheFileConfigs, '<?php'.PHP_EOL.'    return new \Klipper\Component\Security\Permission\PermissionConfigCollection();'.PHP_EOL);

        $cacheFactory = new CachePermissionFactory($this->factory, [
            'cache_dir' => $this->cacheDir,
        ]);
        $cacheFactory->setConfigCacheFactory($this->configCacheFactory);

        $this->factory->expects(static::once())
            ->method('createConfigurations')
        ;

        $cache = $this->getMockBuilder(ConfigCacheInterface::class)->getMock();
        $cache->expects(static::once())
            ->method('write')
        ;
        $cache->expects(static::once())
            ->method('getPath')
            ->willReturn($cacheFileConfigs)
        ;

        $this->configCacheFactory->expects(static::atLeastOnce())
            ->method('cache')
            ->willReturnCallback(static function ($file, $callable) use ($cache) {
                $callable($cache);

                return $cache;
            })
        ;

        $cacheFactory->createConfigurations();
    }

    public function testWarmUpWithoutCacheDir(): void
    {
        $cacheFactory = new CachePermissionFactory($this->factory, [
            'cache_dir' => null,
        ]);
        $cacheFactory->setConfigCacheFactory($this->configCacheFactory);

        $this->factory->expects(static::never())
            ->method('createConfigurations')
        ;

        $cacheFactory->warmUp('cache_dir');
    }

    public function testWarmUpWithCacheDir(): void
    {
        $cacheFactory = new CachePermissionFactory($this->factory, [
            'cache_dir' => $this->cacheDir,
            'debug' => true,
        ]);
        $cacheFactory->setConfigCacheFactory($this->configCacheFactory);

        $this->factory->expects(static::once())
            ->method('createConfigurations')
        ;

        $cacheFactory->warmUp('cache_dir');
    }
}
