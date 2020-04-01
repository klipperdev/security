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

use Klipper\Component\Security\Permission\PermissionConfigCollection;
use Klipper\Component\Security\Permission\PermissionConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class PermissionConfigCollectionTest extends TestCase
{
    public function testAdd(): void
    {
        /** @var MockObject|PermissionConfigInterface $config1 */
        $config1 = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();
        $config1->expects(static::atLeastOnce())
            ->method('getType')
            ->willReturn(\stdClass::class)
        ;

        /** @var MockObject|PermissionConfigInterface $config2 */
        $config2 = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();
        $config2->expects(static::atLeastOnce())
            ->method('getType')
            ->willReturn(\stdClass::class)
        ;

        $config1->expects(static::once())
            ->method('merge')
            ->with($config2)
        ;

        $collection = new PermissionConfigCollection();

        static::assertCount(0, $collection->all());

        $collection->add($config1);
        static::assertCount(1, $collection->all());

        $collection->add($config2);
        static::assertCount(1, $collection->all());

        static::assertSame($config1, $collection->get(\stdClass::class));
    }

    public function testRemove(): void
    {
        /** @var MockObject|PermissionConfigInterface $config1 */
        $config1 = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();
        $config1->expects(static::atLeastOnce())
            ->method('getType')
            ->willReturn(\stdClass::class)
        ;

        $collection = new PermissionConfigCollection();

        static::assertCount(0, $collection->all());

        $collection->add($config1);
        static::assertCount(1, $collection->all());

        $collection->remove(\stdClass::class);
        static::assertCount(0, $collection->all());
    }

    public function testAddCollection(): void
    {
        /** @var MockObject|PermissionConfigInterface $config1 */
        $config1 = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();
        $config1->expects(static::atLeastOnce())
            ->method('getType')
            ->willReturn(\stdClass::class)
        ;

        $collection1 = new PermissionConfigCollection();
        static::assertCount(0, $collection1->all());

        $collection2 = new PermissionConfigCollection();
        $collection2->add($config1);
        static::assertCount(1, $collection2->all());

        $collection1->addCollection($collection2);
        static::assertCount(1, $collection1->all());
    }
}
