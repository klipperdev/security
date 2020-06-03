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

use Klipper\Component\Security\Exception\UnexpectedTypeException;
use Klipper\Component\Security\Identity\SubjectIdentityInterface;
use Klipper\Component\Security\Permission\FieldVote;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class FieldVoteTest extends TestCase
{
    public function testFieldVote(): void
    {
        $object = new MockObject('foo');
        $field = 'name';

        $fv = new FieldVote($object, $field);

        static::assertNotNull($fv->getSubject());
        static::assertSame($object, $fv->getSubject()->getObject());
        static::assertSame(\get_class($object), $fv->getSubject()->getType());
        static::assertSame($field, $fv->getField());
    }

    public function testFieldVoteWithSubjectIdentity(): void
    {
        $object = $this->getMockBuilder(SubjectIdentityInterface::class)->getMock();
        $field = 'name';

        $fv = new FieldVote($object, $field);

        static::assertSame($object, $fv->getSubject());
        static::assertSame($field, $fv->getField());
    }

    public function testFieldVoteWithClassname(): void
    {
        $object = \stdClass::class;
        $field = 'field';

        $fv = new FieldVote($object, $field);

        static::assertNull($fv->getSubject()->getObject());
        static::assertSame(\stdClass::class, $fv->getSubject()->getType());
        static::assertSame($field, $fv->getField());
    }

    public function testFieldVoteWithInvalidSubject(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "Klipper\\Component\\Security\\Identity\\SubjectIdentityInterface|object|string", "integer" given');

        $object = 42;
        $field = 'field';

        new FieldVote($object, $field);
    }
}
