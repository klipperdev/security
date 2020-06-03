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

use Klipper\Component\Security\Sharing\CacheSharingFactory;
use Klipper\Component\Security\Sharing\SharingFactoryInterface;
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
final class CacheSharingFactoryTest extends TestCase
{
    /**
     * @var MockObject|SharingFactoryInterface
     */
    private $factory;

    /**
     * @var ConfigCacheFactoryInterface|MockObject
     */
    private $configCacheFactory;

    private ?string $cacheDir = null;

    protected function setUp(): void
    {
        $this->factory = $this->getMockBuilder(SharingFactoryInterface::class)->getMock();
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
        $cacheFactory = new CacheSharingFactory($this->factory, [
            'cache_dir' => null,
        ]);
        $cacheFactory->setConfigCacheFactory($this->configCacheFactory);

        $this->factory->expects(static::once())
            ->method('createSubjectConfigurations')
        ;

        $this->factory->expects(static::once())
            ->method('createIdentityConfigurations')
        ;

        $this->configCacheFactory->expects(static::never())
            ->method('cache')
        ;

        $cacheFactory->createSubjectConfigurations();
        $cacheFactory->createIdentityConfigurations();
    }

    public function testCreateConfigsWithDebug(): void
    {
        $cacheFactory = new CacheSharingFactory($this->factory, [
            'debug' => true,
        ]);
        $cacheFactory->setConfigCacheFactory($this->configCacheFactory);

        $this->factory->expects(static::once())
            ->method('createSubjectConfigurations')
        ;

        $this->factory->expects(static::once())
            ->method('createIdentityConfigurations')
        ;

        $this->configCacheFactory->expects(static::never())
            ->method('cache')
        ;

        $cacheFactory->createSubjectConfigurations();
        $cacheFactory->createIdentityConfigurations();
    }

    public function testCreateConfigsWithCacheDir(): void
    {
        $fs = new Filesystem();

        $cacheFileSubjects = $this->cacheDir.'/cache_file_subjects.php';
        $fs->dumpFile($cacheFileSubjects, '<?php'.PHP_EOL.'    return new \Klipper\Component\Security\Sharing\SharingSubjectConfigCollection();'.PHP_EOL);

        $cacheFileIdentities = $this->cacheDir.'/cache_file_identities.php';
        $fs->dumpFile($cacheFileIdentities, '<?php'.PHP_EOL.'    return new \Klipper\Component\Security\Sharing\SharingIdentityConfigCollection();'.PHP_EOL);

        $cacheFactory = new CacheSharingFactory($this->factory, [
            'cache_dir' => $this->cacheDir,
        ]);
        $cacheFactory->setConfigCacheFactory($this->configCacheFactory);

        $this->factory->expects(static::once())
            ->method('createSubjectConfigurations')
        ;

        $this->factory->expects(static::once())
            ->method('createIdentityConfigurations')
        ;

        $cache = $this->getMockBuilder(ConfigCacheInterface::class)->getMock();
        $cache->expects(static::at(0))
            ->method('write')
        ;
        $cache->expects(static::at(1))
            ->method('getPath')
            ->willReturn($cacheFileSubjects)
        ;
        $cache->expects(static::at(2))
            ->method('write')
        ;
        $cache->expects(static::at(3))
            ->method('getPath')
            ->willReturn($cacheFileIdentities)
        ;

        $this->configCacheFactory->expects(static::atLeastOnce())
            ->method('cache')
            ->willReturnCallback(static function ($file, $callable) use ($cache) {
                $callable($cache);

                return $cache;
            })
        ;

        $cacheFactory->createSubjectConfigurations();
        $cacheFactory->createIdentityConfigurations();
    }

    public function testWarmUpWithoutCacheDir(): void
    {
        $cacheFactory = new CacheSharingFactory($this->factory, [
            'cache_dir' => null,
        ]);
        $cacheFactory->setConfigCacheFactory($this->configCacheFactory);

        $this->factory->expects(static::never())
            ->method('createSubjectConfigurations')
        ;

        $this->factory->expects(static::never())
            ->method('createIdentityConfigurations')
        ;

        $cacheFactory->warmUp('cache_dir');
    }

    public function testWarmUpWithCacheDir(): void
    {
        $cacheFactory = new CacheSharingFactory($this->factory, [
            'cache_dir' => $this->cacheDir,
            'debug' => true,
        ]);
        $cacheFactory->setConfigCacheFactory($this->configCacheFactory);

        $this->factory->expects(static::once())
            ->method('createSubjectConfigurations')
        ;

        $this->factory->expects(static::once())
            ->method('createIdentityConfigurations')
        ;

        $cacheFactory->warmUp('cache_dir');
    }
}
