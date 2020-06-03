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

use Doctrine\Common\Annotations\AnnotationReader;
use Klipper\Component\Config\Loader\ClassFinder;
use Klipper\Component\Security\Sharing\Loader\IdentityAnnotationLoader;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObjectWithAnnotation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class IdentityAnnotationLoaderTest extends TestCase
{
    public function testSupports(): void
    {
        $reader = new AnnotationReader();
        $loader = new IdentityAnnotationLoader($reader);

        static::assertTrue($loader->supports(__DIR__, 'annotation'));
        static::assertFalse($loader->supports(__DIR__, 'config'));
        static::assertFalse($loader->supports(new \stdClass(), 'annotation'));
    }

    /**
     * @throws
     */
    public function testLoad(): void
    {
        /** @var ClassFinder|MockObject $finder */
        $finder = $this->getMockBuilder(ClassFinder::class)
            ->setMethods(['findClasses'])
            ->getMock()
        ;

        $finder->expects(static::once())
            ->method('findClasses')
            ->willReturn([
                MockObjectWithAnnotation::class,
                'InvalidClass',
            ])
        ;

        $reader = new AnnotationReader();
        $loader = new IdentityAnnotationLoader($reader, $finder);

        $configs = $loader->load(__DIR__, 'annotation');

        static::assertCount(1, $configs);

        $config = current($configs->all());
        static::assertSame(MockObjectWithAnnotation::class, $config->getType());
        static::assertSame('object', $config->getAlias());
        static::assertTrue($config->isRoleable());
        static::assertTrue($config->isPermissible());
    }
}
