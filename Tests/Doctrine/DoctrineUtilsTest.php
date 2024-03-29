<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOPgSql\Driver;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Klipper\Component\Security\Doctrine\DoctrineUtils;
use Klipper\Component\Security\Exception\RuntimeException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class DoctrineUtilsTest extends TestCase
{
    public function testGetIdentifier(): void
    {
        /** @var ClassMetadata|MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier')
        ;

        $targetClass->expects(static::once())
            ->method('getIdentifierFieldNames')
            ->willReturn([
                'identifier',
                'next',
            ])
        ;

        static::assertSame('identifier', DoctrineUtils::getIdentifier($targetClass));
        DoctrineUtils::clearCaches();
    }

    public function testGetIdentifierWithoutIdentifier(): void
    {
        /** @var ClassMetadata|MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier')
        ;

        $targetClass->expects(static::once())
            ->method('getIdentifierFieldNames')
            ->willReturn([])
        ;

        static::assertSame('id', DoctrineUtils::getIdentifier($targetClass));
        DoctrineUtils::clearCaches();
    }

    public function getFieldTypes(): array
    {
        return [
            [Types::GUID, '00000000-0000-0000-0000-000000000000'],
            [Types::STRING, ''],
            [Types::TEXT, ''],
            [Types::INTEGER, 0],
            [Types::SMALLINT, 0],
            [Types::BIGINT, 0],
            [Types::DECIMAL, 0],
            [Types::FLOAT, 0],
            [Types::BINARY, null],
            [Types::BLOB, null],
        ];
    }

    /**
     * @dataProvider getFieldTypes
     *
     * @param string     $type       The doctrine field type
     * @param int|string $validValue The valid value
     */
    public function testGetMockZeroId(string $type, $validValue): void
    {
        /** @var ClassMetadata|MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier')
        ;

        $targetClass->expects(static::once())
            ->method('getIdentifierFieldNames')
            ->willReturn([
                'id',
            ])
        ;

        $targetClass->expects(static::once())
            ->method('getTypeOfField')
            ->with('id')
            ->willReturn($type)
        ;

        static::assertSame($validValue, DoctrineUtils::getMockZeroId($targetClass));
        DoctrineUtils::clearCaches();
    }

    /**
     * @throws
     */
    public function testCastIdentifier(): void
    {
        /** @var ClassMetadata|MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier')
        ;

        $targetClass->expects(static::atLeastOnce())
            ->method('getIdentifierFieldNames')
            ->willReturn([
                'id',
            ])
        ;

        $targetClass->expects(static::once())
            ->method('getTypeOfField')
            ->with('id')
            ->willReturn(Types::GUID)
        ;

        $dbPlatform = $this->getMockForAbstractClass(
            AbstractPlatform::class,
            [],
            '',
            true,
            true,
            true,
            [
                'getGuidTypeDeclarationSQL',
            ]
        );
        $dbPlatform->expects(static::once())
            ->method('getGuidTypeDeclarationSQL')
            ->with(['id'])
            ->willReturn('UUID')
        ;

        /** @var Connection|MockObject $conn */
        $conn = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $conn->expects(static::atLeastOnce())
            ->method('getDatabasePlatform')
            ->willReturn($dbPlatform)
        ;
        $conn->expects(static::atLeastOnce())
            ->method('getDriver')
            ->willReturn($this->getMockBuilder(Driver::class)->disableOriginalConstructor()->getMock())
        ;

        static::assertSame('::UUID', DoctrineUtils::castIdentifier($targetClass, $conn));
        DoctrineUtils::clearCaches();
    }

    /**
     * @throws
     */
    public function testGetIdentifierTypeWithTypeString(): void
    {
        /** @var ClassMetadata|MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier')
        ;

        $targetClass->expects(static::once())
            ->method('getIdentifierFieldNames')
            ->willReturn([
                'id',
            ])
        ;

        $type = Type::getType(Types::GUID);

        $targetClass->expects(static::once())
            ->method('getTypeOfField')
            ->with('id')
            ->willReturn($type)
        ;

        static::assertEquals($type, DoctrineUtils::getIdentifierType($targetClass));
        DoctrineUtils::clearCaches();
    }

    /**
     * @throws
     */
    public function testGetIdentifierTypeWithTypeInstance(): void
    {
        /** @var ClassMetadata|MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier')
        ;

        $targetClass->expects(static::once())
            ->method('getIdentifierFieldNames')
            ->willReturn([
                'id',
            ])
        ;

        $type = Type::getType(Types::GUID);

        $targetClass->expects(static::once())
            ->method('getTypeOfField')
            ->with('id')
            ->willReturn($type)
        ;

        static::assertSame($type, DoctrineUtils::getIdentifierType($targetClass));
        DoctrineUtils::clearCaches();
    }

    /**
     * @throws
     */
    public function testGetIdentifierTypeWithInvalidType(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The Doctrine DBAL type is not found for "TestIdentifier::id" identifier');

        /** @var ClassMetadata|MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier')
        ;

        $targetClass->expects(static::once())
            ->method('getIdentifierFieldNames')
            ->willReturn([
                'id',
            ])
        ;

        static::assertSame(42, DoctrineUtils::getIdentifierType($targetClass));
        DoctrineUtils::clearCaches();
    }
}
