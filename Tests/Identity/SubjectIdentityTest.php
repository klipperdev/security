<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Identity;

use Klipper\Component\Security\Identity\SubjectIdentity;
use Klipper\Component\Security\Identity\SubjectIdentityInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObject;
use Klipper\Component\Security\Tests\Fixtures\Model\MockSubjectObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SubjectIdentityTest extends TestCase
{
    public function testDebugInfo(): void
    {
        $object = new MockObject('foo');

        $si = new SubjectIdentity(\get_class($object), (string) $object->getId(), $object);

        static::assertSame('SubjectIdentity(Klipper\Component\Security\Tests\Fixtures\Model\MockObject, 42)', (string) $si);
    }

    public function testTypeAndIdentifier(): void
    {
        $object = new MockObject('foo');

        $si = new SubjectIdentity(\get_class($object), (string) $object->getId(), $object);

        static::assertSame((string) $object->getId(), $si->getIdentifier());
        static::assertSame(MockObject::class, $si->getType());
        static::assertSame($object, $si->getObject());
    }

    public function testEmptyType(): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The type cannot be empty');

        new SubjectIdentity(null, '42');
    }

    public function testEmptyIdentifier(): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The identifier cannot be empty');

        new SubjectIdentity(MockObject::class, '');
    }

    public function testInvalidSubject(): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "object|null", "integer" given');

        new SubjectIdentity(MockObject::class, '42', 42);
    }

    public function getIdentities(): array
    {
        return [
            [new SubjectIdentity(MockObject::class, '42'), true],
            [new SubjectIdentity(\stdClass::class, '42'), false],
            [new SubjectIdentity(MockObject::class, '42', new MockObject('foo')), true],
            [new SubjectIdentity(MockObject::class, '50', new MockObject('foo', 50)), false],
        ];
    }

    /**
     * @dataProvider getIdentities
     *
     * @param mixed $value  The value
     * @param bool  $result The expected result
     */
    public function testEquals($value, $result): void
    {
        $object = new MockObject('foo');
        $si = new SubjectIdentity(\get_class($object), (string) $object->getId(), $object);

        static::assertSame($result, $si->equals($value));
    }

    public function testFromClassname(): void
    {
        $si = SubjectIdentity::fromClassname(MockObject::class);

        static::assertSame(MockObject::class, $si->getType());
        static::assertSame('class', $si->getIdentifier());
        static::assertNull($si->getObject());
    }

    public function testFromClassnameWithNonExistentClass(): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\InvalidSubjectIdentityException::class);
        $this->expectExceptionMessage('The class "FooBar" does not exist');

        SubjectIdentity::fromClassname('FooBar');
    }

    public function testFromObject(): void
    {
        $object = new MockObject('foo');

        $si = SubjectIdentity::fromObject($object);

        static::assertSame(MockObject::class, $si->getType());
        static::assertSame((string) $object->getId(), $si->getIdentifier());
        static::assertSame($object, $si->getObject());
    }

    public function testFromObjectWithSubjectInstance(): void
    {
        $object = new MockSubjectObject('foo');

        $si = SubjectIdentity::fromObject($object);

        static::assertSame(MockSubjectObject::class, $si->getType());
        static::assertSame((string) $object->getSubjectIdentifier(), $si->getIdentifier());
        static::assertSame($object, $si->getObject());
    }

    public function testFromObjectWithSubjectIdentityInstance(): void
    {
        $object = $this->getMockBuilder(SubjectIdentityInterface::class)->getMock();

        $si = SubjectIdentity::fromObject($object);

        static::assertSame($object, $si);
    }

    public function testFromObjectWithNonObject(): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\InvalidSubjectIdentityException::class);
        $this->expectExceptionMessage('Expected argument of type "object", "integer" given');

        /** @var object $object */
        $object = 42;

        SubjectIdentity::fromObject($object);
    }

    public function testFromObjectWithEmptyIdentifier(): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\InvalidSubjectIdentityException::class);
        $this->expectExceptionMessage('The identifier cannot be empty');

        $object = new MockObject('foo', null);

        SubjectIdentity::fromObject($object);
    }

    public function testFromObjectWithInvalidObject(): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\InvalidSubjectIdentityException::class);
        $this->expectExceptionMessage('The object must either implement the SubjectInterface, or have a method named "getId"');

        $object = new \stdClass();

        SubjectIdentity::fromObject($object);
    }
}
