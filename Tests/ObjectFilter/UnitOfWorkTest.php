<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\ObjectFilter;

use Klipper\Component\Security\ObjectFilter\UnitOfWork;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class UnitOfWorkTest extends TestCase
{
    public function testGetObjectIdentifiers(): void
    {
        $uow = new UnitOfWork();

        static::assertCount(0, $uow->getObjectIdentifiers());
    }

    public function testAttachAndDetach(): void
    {
        $uow = new UnitOfWork();
        $obj = new MockObject('foo');

        static::assertCount(0, $uow->getObjectIdentifiers());

        $uow->attach($obj);
        static::assertCount(1, $uow->getObjectIdentifiers());

        $uow->detach($obj);
        static::assertCount(0, $uow->getObjectIdentifiers());
    }

    public function testAttachExistingObject(): void
    {
        $uow = new UnitOfWork();
        $obj = new MockObject('foo');

        static::assertCount(0, $uow->getObjectIdentifiers());

        $uow->attach($obj);
        static::assertCount(1, $uow->getObjectIdentifiers());

        $uow->attach($obj);
        static::assertCount(1, $uow->getObjectIdentifiers());
    }

    public function testDetachNonExistingObject(): void
    {
        $uow = new UnitOfWork();
        $obj = new MockObject('foo');

        static::assertCount(0, $uow->getObjectIdentifiers());

        $uow->detach($obj);
        static::assertCount(0, $uow->getObjectIdentifiers());
    }

    public function testFlush(): void
    {
        $uow = new UnitOfWork();
        $obj1 = new MockObject('foo');
        $obj2 = new MockObject('bar');

        $uow->attach($obj1);
        $uow->attach($obj2);
        static::assertCount(2, $uow->getObjectIdentifiers());

        $uow->flush();
        static::assertCount(0, $uow->getObjectIdentifiers());
    }

    public function testGetObjectChangeSet(): void
    {
        $uow = new UnitOfWork();
        $obj = new MockObject('foo');

        static::assertCount(0, $uow->getObjectIdentifiers());

        $uow->attach($obj);
        static::assertCount(1, $uow->getObjectIdentifiers());

        $obj->setName('bar');

        $valid = [
            'name' => [
                'old' => 'foo',
                'new' => 'bar',
            ],
        ];

        static::assertSame($valid, $uow->getObjectChangeSet($obj));
    }

    public function testGetObjectChangeSetWithNonExistingObject(): void
    {
        $uow = new UnitOfWork();
        $obj = new MockObject('foo');

        static::assertCount(0, $uow->getObjectIdentifiers());
        static::assertSame([], $uow->getObjectChangeSet($obj));
    }
}
