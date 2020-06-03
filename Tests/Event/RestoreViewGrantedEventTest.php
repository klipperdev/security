<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Event;

use Klipper\Component\Security\Event\RestoreViewGrantedEvent;
use Klipper\Component\Security\Exception\UnexpectedTypeException;
use Klipper\Component\Security\Permission\FieldVote;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class RestoreViewGrantedEventTest extends TestCase
{
    public function testEvent(): void
    {
        $object = new MockObject('foo');
        $fieldVote = new FieldVote($object, 'name');
        $oldValue = 'bar';
        $newValue = $object->getName();

        $event = new RestoreViewGrantedEvent($fieldVote, $oldValue, $newValue);

        static::assertSame($fieldVote, $event->getFieldVote());
        static::assertSame($fieldVote->getSubject()->getObject(), $event->getObject());
        static::assertSame($oldValue, $event->getOldValue());
        static::assertSame($newValue, $event->getNewValue());
        static::assertFalse($event->isSkipAuthorizationChecker());
        static::assertTrue($event->isGranted());

        $event->setGranted(false);
        static::assertTrue($event->isSkipAuthorizationChecker());
        static::assertFalse($event->isGranted());
    }

    public function testEventWithInvalidFieldVote(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "object", "NULL" given');

        $object = \stdClass::class;
        $fieldVote = new FieldVote($object, 'foo');
        $oldValue = 23;
        $newValue = 46;

        new RestoreViewGrantedEvent($fieldVote, $oldValue, $newValue);
    }
}
