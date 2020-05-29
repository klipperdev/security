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
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Klipper\Component\Security\Doctrine\ORM\Listener\PermissionCheckerListener;
use Klipper\Component\Security\Permission\PermissionManagerInterface;
use Klipper\Component\Security\Token\ConsoleToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class PermissionCheckerListenerTest extends TestCase
{
    /**
     * @var MockObject|TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface|MockObject
     */
    protected $authChecker;

    /**
     * @var MockObject|PermissionManagerInterface
     */
    protected $permissionManager;

    /**
     * @var EntityManagerInterface|MockObject
     */
    protected $em;

    /**
     * @var MockObject|UnitOfWork
     */
    protected $uow;

    /**
     * @var PermissionCheckerListener
     */
    protected $listener;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();
        $this->permissionManager = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->uow = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
        $this->listener = new PermissionCheckerListener();

        $this->em->expects(static::any())
            ->method('getUnitOfWork')
            ->willReturn($this->uow)
        ;

        $this->listener->setTokenStorage($this->tokenStorage);
        $this->listener->setAuthorizationChecker($this->authChecker);
        $this->listener->setPermissionManager($this->permissionManager);

        static::assertCount(2, $this->listener->getSubscribedEvents());
    }

    public function getInvalidInitMethods(): array
    {
        return [
            ['setTokenStorage', []],
            ['setAuthorizationChecker', ['tokenStorage']],
            ['setPermissionManager', ['tokenStorage', 'authChecker']],
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

        $msg = sprintf('The "%s()" method must be called before the init of the "Klipper\Component\Security\Doctrine\ORM\Listener\PermissionCheckerListener" class', $method);
        $this->expectExceptionMessage($msg);

        $listener = new PermissionCheckerListener();

        if (\in_array('tokenStorage', $setters, true)) {
            $listener->setTokenStorage($this->tokenStorage);
        }

        if (\in_array('authChecker', $setters, true)) {
            $listener->setAuthorizationChecker($this->authChecker);
        }

        if (\in_array('permissionManager', $setters, true)) {
            $listener->setPermissionManager($this->permissionManager);
        }

        /** @var OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();

        $listener->onFlush($args);
    }

    public function testPostFlush(): void
    {
        $this->permissionManager->expects(static::once())
            ->method('resetPreloadPermissions')
            ->with([])
        ;

        $this->listener->postFlush();
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

        $this->listener->onFlush($args);
    }

    public function testOnFLushWithInsufficientPrivilegeToCreateEntity(): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\AccessDeniedException::class);
        $this->expectExceptionMessage('Insufficient privilege to create the entity');

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

        $this->permissionManager->expects(static::once())
            ->method('preloadPermissions')
            ->with([$object])
        ;

        $this->authChecker->expects(static::once())
            ->method('isGranted')
            ->with('perm:create', $object)
            ->willReturn(false)
        ;

        $this->listener->onFlush($args);
    }

    public function testOnFLushWithInsufficientPrivilegeToUpdateEntity(): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\AccessDeniedException::class);
        $this->expectExceptionMessage('Insufficient privilege to update the entity');

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

        $this->permissionManager->expects(static::once())
            ->method('preloadPermissions')
            ->with([$object])
        ;

        $this->authChecker->expects(static::once())
            ->method('isGranted')
            ->with('perm:update', $object)
            ->willReturn(false)
        ;

        $this->listener->onFlush($args);
    }

    public function testOnFLushWithInsufficientPrivilegeToDeleteEntity(): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\AccessDeniedException::class);
        $this->expectExceptionMessage('Insufficient privilege to delete the entity');

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

        $this->permissionManager->expects(static::once())
            ->method('preloadPermissions')
            ->with([$object])
        ;

        $this->authChecker->expects(static::once())
            ->method('isGranted')
            ->with('perm:delete', $object)
            ->willReturn(false)
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

        $this->permissionManager->expects(static::once())
            ->method('preloadPermissions')
            ->with([])
        ;

        $this->listener->onFlush($args);
    }
}
