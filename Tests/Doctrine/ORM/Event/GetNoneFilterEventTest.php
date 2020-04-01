<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Doctrine\ORM\Event;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Query\FilterCollection;
use Klipper\Component\Security\Doctrine\ORM\Event\GetNoneFilterEvent;
use Klipper\Component\Security\Tests\Fixtures\Model\MockSharing;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class GetNoneFilterEventTest extends TestCase
{
    /**
     * @var EntityManagerInterface|MockObject
     */
    protected $entityManager;

    /**
     * @var Connection|MockObject
     */
    protected $connection;

    /**
     * @var ClassMetadata|MockObject
     */
    protected $targetEntity;

    /**
     * @var MockObject|SQLFilter
     */
    protected $filter;

    /**
     * @var GetNoneFilterEvent
     */
    protected $event;

    /**
     * @throws
     */
    protected function setUp(): void
    {
        $this->entityManager = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $this->targetEntity = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $this->filter = $this->getMockForAbstractClass(SQLFilter::class, [$this->entityManager]);

        $this->entityManager->expects(static::any())
            ->method('getFilters')
            ->willReturn(new FilterCollection($this->entityManager))
        ;

        $this->entityManager->expects(static::any())
            ->method('getConnection')
            ->willReturn($this->connection)
        ;

        $this->entityManager->expects(static::any())
            ->method('getClassMetadata')
            ->willReturn($this->targetEntity)
        ;

        $this->connection->expects(static::any())
            ->method('quote')
            ->willReturnCallback(static function ($v) {
                return '\''.$v.'\'';
            })
        ;

        $this->event = new GetNoneFilterEvent(
            $this->filter,
            $this->entityManager,
            $this->targetEntity,
            't0',
            MockSharing::class
        );
    }

    public function testGetters(): void
    {
        static::assertSame($this->entityManager, $this->event->getEntityManager());
        static::assertSame($this->entityManager->getConnection(), $this->event->getConnection());
        static::assertSame($this->entityManager->getClassMetadata(MockSharing::class), $this->event->getClassMetadata(MockSharing::class));
        static::assertSame($this->entityManager->getClassMetadata(MockSharing::class), $this->event->getSharingClassMetadata());
        static::assertSame($this->targetEntity, $this->event->getTargetEntity());
        static::assertSame('t0', $this->event->getTargetTableAlias());
    }

    /**
     * @throws
     */
    public function testSetParameter(): void
    {
        static::assertFalse($this->event->hasParameter('foo'));
        $this->event->setParameter('foo', true, 'boolean');
        static::assertSame('\'1\'', $this->event->getParameter('foo'));
        static::assertTrue($this->event->getRealParameter('foo'));
    }

    public function testSetFilterConstraint(): void
    {
        static::assertSame('', $this->event->getFilterConstraint());

        $this->event->setFilterConstraint('TEST_FILTER');

        static::assertSame('TEST_FILTER', $this->event->getFilterConstraint());
    }
}
