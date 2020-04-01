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

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Klipper\Component\Config\Loader\ClassFinder;
use Klipper\Component\Security\Permission\Loader\AnnotationLoader;
use Klipper\Component\Security\Permission\PermissionConfigInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObjectWithAnnotation;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObjectWithOnlyFieldAnnotation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class AnnotationLoaderTest extends TestCase
{
    protected function setUp(): void
    {
        AnnotationRegistry::registerLoader('class_exists');
    }

    public function testSupports(): void
    {
        $reader = new AnnotationReader();
        $loader = new AnnotationLoader($reader);

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
                MockObjectWithOnlyFieldAnnotation::class,
                'InvalidClass',
            ])
        ;

        $reader = new AnnotationReader();
        $loader = new AnnotationLoader($reader, $finder);
        /** @var PermissionConfigInterface[] $configs */
        $configs = $loader->load(__DIR__, 'annotation');

        static::assertCount(2, $configs);

        foreach ($configs as $config) {
            static::assertInstanceOf(PermissionConfigInterface::class, $config);

            if (MockObjectWithAnnotation::class === $config->getType()) {
                static::assertSame(MockObjectWithAnnotation::class, $config->getType());
                static::assertSame(['view', 'create', 'update', 'delete'], $config->getOperations());
                static::assertSame('foo', $config->getMaster());

                static::assertCount(2, $config->getFields());
                static::assertTrue($config->hasField('id'));
                static::assertTrue($config->hasField('name'));

                static::assertSame(['read', 'view'], $config->getField('id')->getOperations());
                static::assertSame(['read', 'edit'], $config->getField('name')->getOperations());
            } elseif (MockObjectWithOnlyFieldAnnotation::class === $config->getType()) {
                static::assertSame(MockObjectWithOnlyFieldAnnotation::class, $config->getType());
                static::assertSame([], $config->getOperations());

                static::assertCount(1, $config->getFields());
                static::assertTrue($config->hasField('name'));

                static::assertSame(['read', 'edit'], $config->getField('name')->getOperations());
            }
        }
    }
}
