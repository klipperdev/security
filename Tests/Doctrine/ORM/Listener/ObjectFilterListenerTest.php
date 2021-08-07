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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Klipper\Component\Security\Doctrine\ORM\Listener\ObjectFilterListener;
use Klipper\Component\Security\ObjectFilter\ObjectFilterInterface;
use Klipper\Component\Security\Permission\PermissionManagerInterface;
use Klipper\Component\Security\Token\ConsoleToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class ObjectFilterListenerTest extends TestCase
{
    /**
     * @var MockObject|TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var MockObject|PermissionManagerInterface
     */
    protected $permissionManager;

    /**
     * @var MockObject|ObjectFilterInterface
     */
    protected $objectFilter;

    /**
     * @var EntityManagerInterface|MockObject
     */
    protected $em;

    /**
     * @var MockObject|UnitOfWork
     */
    protected $uow;

    protected ?ObjectFilterListener $listener = null;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->permissionManager = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
        $this->objectFilter = $this->getMockBuilder(ObjectFilterInterface::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->uow = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
        $this->listener = new ObjectFilterListener(
            $this->permissionManager,
            $this->tokenStorage,
            $this->objectFilter
        );

        $this->em->expects(static::any())
            ->method('getUnitOfWork')
            ->willReturn($this->uow)
        ;

        static::assertCount(3, $this->listener->getSubscribedEvents());
    }

    public function testPostFlush(): void
    {
        $this->permissionManager->expects(static::once())
            ->method('resetPreloadPermissions')
            ->with([])
        ;

        $this->listener->postFlush();
    }

    public function testPostLoadWithDisabledPermissionManager(): void
    {
        /** @var LifecycleEventArgs $args */
        $args = $this->getMockBuilder(LifecycleEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $this->tokenStorage->expects(static::once())
            ->method('getToken')
            ->willReturn($token)
        ;

        $this->permissionManager->expects(static::once())
            ->method('isEnabled')
            ->willReturn(false)
        ;

        $this->objectFilter->expects(static::never())
            ->method('filter')
        ;

        $this->listener->postLoad($args);
    }

    public function testPostLoadWithEmptyToken(): void
    {
        /** @var LifecycleEventArgs $args */
        $args = $this->getMockBuilder(LifecycleEventArgs::class)->disableOriginalConstructor()->getMock();

        $this->tokenStorage->expects(static::once())
            ->method('getToken')
            ->willReturn(null)
        ;

        $this->objectFilter->expects(static::never())
            ->method('filter')
        ;

        $this->listener->postLoad($args);
    }

    public function testPostLoadWithConsoleToken(): void
    {
        /** @var LifecycleEventArgs $args */
        $args = $this->getMockBuilder(LifecycleEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(ConsoleToken::class)->disableOriginalConstructor()->getMock();

        $this->tokenStorage->expects(static::once())
            ->method('getToken')
            ->willReturn($token)
        ;

        $this->objectFilter->expects(static::never())
            ->method('filter')
        ;

        $this->listener->postLoad($args);
    }

    public function testPostLoad(): void
    {
        /** @var LifecycleEventArgs|MockObject $args */
        $args = $this->getMockBuilder(LifecycleEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $entity = new \stdClass();

        $this->tokenStorage->expects(static::once())
            ->method('getToken')
            ->willReturn($token)
        ;

        $this->permissionManager->expects(static::once())
            ->method('isEnabled')
            ->willReturn(true)
        ;

        $args->expects(static::once())
            ->method('getEntity')
            ->willReturn($entity)
        ;

        $this->objectFilter->expects(static::once())
            ->method('filter')
            ->with($entity)
        ;

        $this->listener->postLoad($args);
    }

    public function testOnFlushWithDisabledPermissionManager(): void
    {
        /** @var OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $this->tokenStorage->expects(static::once())
            ->method('getToken')
            ->willReturn($token)
        ;

        $this->permissionManager->expects(static::once())
            ->method('isEnabled')
            ->willReturn(false)
        ;

        $this->objectFilter->expects(static::never())
            ->method('filter')
        ;

        $this->listener->onFlush($args);
    }

    public function testOnFlushWithEmptyToken(): void
    {
        /** @var OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();

        $this->tokenStorage->expects(static::once())
            ->method('getToken')
            ->willReturn(null)
        ;

        $this->objectFilter->expects(static::never())
            ->method('filter')
        ;

        $this->listener->onFlush($args);
    }

    public function testOnFlushWithConsoleToken(): void
    {
        /** @var OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(ConsoleToken::class)->disableOriginalConstructor()->getMock();

        $this->tokenStorage->expects(static::once())
            ->method('getToken')
            ->willReturn($token)
        ;

        $this->objectFilter->expects(static::never())
            ->method('filter')
        ;

        $this->listener->onFlush($args);
    }

    public function testOnFlushWithCreateEntity(): void
    {
        /** @var MockObject|OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $object = $this->getMockBuilder(\stdClass::class)->getMock();

        $this->tokenStorage->expects(static::once())
            ->method('getToken')
            ->willReturn($token)
        ;

        $this->permissionManager->expects(static::once())
            ->method('isEnabled')
            ->willReturn(true)
        ;

        $args->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($this->em)
        ;

        $this->objectFilter->expects(static::once())
            ->method('beginTransaction')
        ;

        $this->uow->expects(static::once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([$object])
        ;

        $this->uow->expects(static::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([])
        ;

        $this->uow->expects(static::once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([])
        ;

        $this->objectFilter->expects(static::once())
            ->method('restore')
        ;

        $this->listener->onFlush($args);
    }

    public function testOnFlushWithUpdateEntity(): void
    {
        /** @var MockObject|OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $object = $this->getMockBuilder(\stdClass::class)->getMock();

        $this->tokenStorage->expects(static::once())
            ->method('getToken')
            ->willReturn($token)
        ;

        $this->permissionManager->expects(static::once())
            ->method('isEnabled')
            ->willReturn(true)
        ;

        $args->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($this->em)
        ;

        $this->objectFilter->expects(static::once())
            ->method('beginTransaction')
        ;

        $this->uow->expects(static::once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([])
        ;

        $this->uow->expects(static::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([$object])
        ;

        $this->uow->expects(static::once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([])
        ;

        $this->objectFilter->expects(static::once())
            ->method('restore')
        ;

        $this->listener->onFlush($args);
    }

    public function testOnFlushWithDeleteEntity(): void
    {
        /** @var MockObject|OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $object = $this->getMockBuilder(\stdClass::class)->getMock();

        $this->tokenStorage->expects(static::once())
            ->method('getToken')
            ->willReturn($token)
        ;

        $this->permissionManager->expects(static::once())
            ->method('isEnabled')
            ->willReturn(true)
        ;

        $args->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($this->em)
        ;

        $this->objectFilter->expects(static::once())
            ->method('beginTransaction')
        ;

        $this->uow->expects(static::once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([])
        ;

        $this->uow->expects(static::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([])
        ;

        $this->uow->expects(static::once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([$object])
        ;

        $this->listener->onFlush($args);
    }

    public function testOnFLush(): void
    {
        /** @var MockObject|OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $this->tokenStorage->expects(static::once())
            ->method('getToken')
            ->willReturn($token)
        ;

        $this->permissionManager->expects(static::once())
            ->method('isEnabled')
            ->willReturn(true)
        ;

        $args->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($this->em)
        ;

        $this->objectFilter->expects(static::once())
            ->method('beginTransaction')
        ;

        $this->uow->expects(static::once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([])
        ;

        $this->uow->expects(static::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([])
        ;

        $this->uow->expects(static::once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([])
        ;

        $this->objectFilter->expects(static::once())
            ->method('commit')
        ;

        $this->listener->onFlush($args);
    }
}
