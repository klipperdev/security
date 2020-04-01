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
use Klipper\Component\Security\Sharing\SharingManagerInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockGroup;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObject;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRole;
use Klipper\Component\Security\Tests\Fixtures\Model\MockSharing;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SharingDeleteListenerTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|SharingManagerInterface
     */
    protected $sharingManager;

    /**
     * @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $em;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|UnitOfWork
     */
    protected $uow;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|QueryBuilder
     */
    protected $qb;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Query
     */
    protected $query;

    /**
     * @var SharingDeleteListener
     */
    protected $listener;

    /**
     * @throws
     */
    protected function setUp(): void
    {
        $this->sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->uow = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
        $this->qb = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();
        $this->listener = new SharingDeleteListener(MockSharing::class);

        $this->listener->setSharingManager($this->sharingManager);

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

    public function getInvalidInitMethods(): array
    {
        return [
            ['setSharingManager', []],
        ];
    }

    /**
     * @dataProvider getInvalidInitMethods
     *
     * @param string   $method  The method
     * @param string[] $setters The setters
     */
    public function testInvalidInit($method, array $setters): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\SecurityException::class);

        $msg = sprintf('The "%s()" method must be called before the init of the "Klipper\Component\Security\Doctrine\ORM\Listener\SharingDeleteListener" class', $method);
        $this->expectExceptionMessage($msg);

        $listener = new SharingDeleteListener(MockSharing::class);

        if (\in_array('sharingManager', $setters, true)) {
            $listener->setSharingManager($this->sharingManager);
        }

        $listener->getSharingManager();
    }

    public function testGetSharingManager(): void
    {
        static::assertSame($this->sharingManager, $this->listener->getSharingManager());
    }

    public function testOnFlush(): void
    {
        /** @var OnFlushEventArgs|\PHPUnit\Framework\MockObject\MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        /** @var \PHPUnit\Framework\MockObject\MockObject|PostFlushEventArgs $postArgs */
        $postArgs = $this->getMockBuilder(PostFlushEventArgs::class)->disableOriginalConstructor()->getMock();

        $args->expects(static::atLeast(1))
            ->method('getEntityManager')
            ->willReturn($this->em)
        ;

        $postArgs->expects(static::atLeast(1))
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

        $this->qb->expects(static::at(0))
            ->method('delete')
            ->with(MockSharing::class, 's')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(1))
            ->method('andWhere')
            ->with('(s.subjectClass = :subjectClass_0 AND s.subjectId IN (:subjectIds_0))')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(2))
            ->method('setParameter')
            ->with('subjectClass_0', MockObject::class)
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(3))
            ->method('setParameter')
            ->with('subjectIds_0', [42, 50])
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(4))
            ->method('andWhere')
            ->with('(s.identityClass = :identityClass_0 AND s.identityName IN (:identityNames_0)) OR (s.identityClass = :identityClass_1 AND s.identityName IN (:identityNames_1))')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(5))
            ->method('setParameter')
            ->with('identityClass_0', MockRole::class)
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(6))
            ->method('setParameter')
            ->with('identityNames_0', [23])
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(7))
            ->method('setParameter')
            ->with('identityClass_1', MockGroup::class)
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(8))
            ->method('setParameter')
            ->with('identityNames_1', [32])
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(9))
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
