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

use Klipper\Component\Security\Sharing\SharingFactory;
use Klipper\Component\Security\Sharing\SharingIdentityConfigCollection;
use Klipper\Component\Security\Sharing\SharingSubjectConfigCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SharingFactoryTest extends TestCase
{
    public function getConfigTypes(): array
    {
        return [
            [SharingSubjectConfigCollection::class, 'createSubjectConfigurations'],
            [SharingIdentityConfigCollection::class, 'createIdentityConfigurations'],
        ];
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testCreateConfigurations(string $collectionClass, string $createMethod): void
    {
        /** @var LoaderInterface|MockObject $subjectLoader */
        $subjectLoader = $this->getMockBuilder(LoaderInterface::class)->getMock();

        /** @var LoaderInterface|MockObject $identityLoader */
        $identityLoader = $this->getMockBuilder(LoaderInterface::class)->getMock();

        $expected = new $collectionClass();
        $loader = 'createSubjectConfigurations' === $createMethod ? $subjectLoader : $identityLoader;
        $loader->expects(static::once())
            ->method('load')
            ->with('resource')
            ->willReturn($expected)
        ;

        $factory = new SharingFactory($subjectLoader, $identityLoader, 'resource');

        static::assertSame($expected, $factory->{$createMethod}());
    }
}
