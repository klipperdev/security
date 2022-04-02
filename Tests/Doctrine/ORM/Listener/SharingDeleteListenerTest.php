<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Doctrine\ORM\Listener;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Klipper\Component\Security\Doctrine\ORM\Listener\SharingDeleteListener;
use Klipper\Component\Security\Model\SharingInterface;
use Klipper\Component\Security\Sharing\SharingManagerInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockGroup;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObject;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRole;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SharingDeleteListenerTest extends TestCase
{
    /**
     * @var MockObject|SharingManagerInterface
     */
    protected $sharingManager;

    /**
     * @var EntityManagerInterface|MockObject
     */
    protected $em;

    /**
     * @var MockObject|UnitOfWork
     */
    protected $uow;

    /**
     * @var MockObject|QueryBuilder
     */
    protected $qb;

    /**
     * @var MockObject|Query
     */
    protected $query;

    protected ?SharingDeleteListener $listener = null;

    /**
     * @throws
     */
    protected function setUp(): void
    {
        $this->sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->uow = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
        $this->qb = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();
        $this->listener = new SharingDeleteListener($this->sharingManager);

        $this->em->expects(static::any())
            ->method('getUnitOfWork')
            ->willReturn($this->uow)
        ;

        $this->query = $this->getMockForAbstractClass(
            AbstractQuery::class,
            [],
            '',
            false,
            false,
            true,
            [
                'execute',
            ]
        );

        static::assertCount(2, $this->listener->getSubscribedEvents());
    }

    public function testOnFlush(): void
    {
        /** @var MockObject|OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();

        /** @var MockObject|PostFlushEventArgs $postArgs */
        $postArgs = $this->getMockBuilder(PostFlushEventArgs::class)->disableOriginalConstructor()->getMock();

        $args->expects(static::exactly(1))
            ->method('getEntityManager')
            ->willReturn($this->em)
        ;

        $postArgs->expects(static::exactly(1))
            ->method('getEntityManager')
            ->willReturn($this->em)
        ;

        // on flush
        $object = new MockObject('foo', 42);
        $object2 = new MockObject('bar', 50);
        $role = new MockRole('ROLE_TEST', 23);
        $group = new MockGroup('GROUP_TEST', 32);
        $deletions = [$object, $role, $object2, $group];

        $this->uow->expects(static::once())
            ->method('getScheduledEntityDeletions')
            ->willReturn($deletions)
        ;

        $this->sharingManager->expects(static::atLeastOnce())
            ->method('hasSubjectConfig')
            ->willReturnCallback(static function ($type) {
                return MockObject::class === $type;
            })
        ;

        $this->sharingManager->expects(static::atLeastOnce())
            ->method('hasIdentityConfig')
            ->willReturnCallback(static function ($type) {
                return MockRole::class === $type || MockGroup::class === $type;
            })
        ;

        // post flush: query builder
        $this->em->expects(static::once())
            ->method('createQueryBuilder')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('delete')
            ->with(SharingInterface::class, 's')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::exactly(2))
            ->method('andWhere')
            ->willReturnMap([
                ['(s.subjectClass = :subjectClass_0 AND s.subjectId IN (:subjectIds_0))', $this->qb],
                ['(s.identityClass = :identityClass_0 AND s.identityName IN (:identityNames_0)) OR (s.identityClass = :identityClass_1 AND s.identityName IN (:identityNames_1))', $this->qb],
            ])
        ;

        $this->qb->expects(static::exactly(6))
            ->method('setParameter')
            ->willReturnMap([
                ['subjectClass_0', MockObject::class, null, $this->qb],
                ['subjectIds_0', [42, 50], null, $this->qb],
                ['identityClass_0', MockRole::class, null, $this->qb],
                ['identityNames_0', [23], null, $this->qb],
                ['identityClass_1', MockGroup::class, null, $this->qb],
                ['identityNames_1', [32], null, $this->qb],
            ])
        ;

        $this->qb->expects(static::once())
            ->method('getQuery')
            ->willReturn($this->query)
        ;

        $this->query->expects(static::once())
            ->method('execute')
        ;

        $this->listener->onFlush($args);
        $this->listener->postFlush($postArgs);
    }
}
