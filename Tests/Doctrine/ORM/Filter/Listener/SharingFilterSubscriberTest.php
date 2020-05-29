<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Doctrine\ORM\Filter\Listener;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\FilterCollection;
use Klipper\Component\Security\Doctrine\ORM\Filter\Listener\SharingFilterSubscriber;
use Klipper\Component\Security\Doctrine\ORM\Filter\SharingFilter;
use Klipper\Component\Security\Identity\RoleSecurityIdentity;
use Klipper\Component\Security\Identity\SecurityIdentityManagerInterface;
use Klipper\Component\Security\Sharing\SharingIdentityConfigInterface;
use Klipper\Component\Security\Sharing\SharingManagerInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRole;
use Klipper\Component\Security\Tests\Fixtures\Model\MockSharing;
use Klipper\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SharingFilterSubscriberTest extends TestCase
{
    /**
     * @var EntityManagerInterface|MockObject
     */
    protected $entityManager;

    /**
     * @var FilterCollection|MockObject
     */
    protected $filterCollection;

    /**
     * @var EventDispatcherInterface|MockObject
     */
    protected $dispatcher;

    /**
     * @var MockObject|TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var MockObject|SecurityIdentityManagerInterface
     */
    protected $sidManager;

    /**
     * @var MockObject|SharingManagerInterface
     */
    protected $sharingManager;

    /**
     * @var string
     */
    protected $sharingClass;

    /**
     * @var SharingFilter
     */
    protected $filter;

    /**
     * @var SharingFilterSubscriber
     */
    protected $listener;

    protected function setUp(): void
    {
        $this->entityManager = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->filterCollection = $this->getMockBuilder(FilterCollection::class)->disableOriginalConstructor()->getMock();
        $this->dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->sidManager = $this->getMockBuilder(SecurityIdentityManagerInterface::class)->getMock();
        $this->sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $this->sharingClass = MockSharing::class;
        $this->filter = new SharingFilter($this->entityManager);
        $this->listener = new SharingFilterSubscriber(
            $this->entityManager,
            $this->dispatcher,
            $this->tokenStorage,
            $this->sidManager,
            $this->sharingManager
        );
        $connection = $this->getMockBuilder(Connection::class)->getMock();
        $connection->expects(static::any())
            ->method('quote')
            ->willReturnCallback(static function ($v) {
                return $v;
            })
        ;

        $this->entityManager->expects(static::any())
            ->method('getFilters')
            ->willReturn($this->filterCollection)
        ;

        $this->entityManager->expects(static::any())
            ->method('getConnection')
            ->willReturn($connection)
        ;

        $this->sharingManager->expects(static::any())
            ->method('isEnabled')
            ->willReturn(true)
        ;

        static::assertCount(4, SharingFilterSubscriber::getSubscribedEvents());
    }

    public function testOnSharingManagerChange(): void
    {
        $this->filterCollection->expects(static::once())
            ->method('getEnabledFilters')
            ->willReturn([
                'sharing' => $this->filter,
            ])
        ;

        $this->sharingManager->expects(static::once())
            ->method('isEnabled')
            ->willReturn(true)
        ;

        static::assertFalse($this->filter->hasParameter('sharing_manager_enabled'));
        $this->listener->onSharingManagerChange();
        static::assertTrue($this->filter->hasParameter('sharing_manager_enabled'));
    }

    public function testOnEventWithoutSecurityIdentities(): void
    {
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $this->filterCollection->expects(static::once())
            ->method('getEnabledFilters')
            ->willReturn([
                'sharing' => $this->filter,
            ])
        ;

        $this->tokenStorage->expects(static::atLeastOnce())
            ->method('getToken')
            ->willReturn($token)
        ;

        $this->sidManager->expects(static::once())
            ->method('getSecurityIdentities')
            ->with($token)
            ->willReturn([])
        ;

        static::assertFalse($this->filter->hasParameter('has_security_identities'));
        static::assertFalse($this->filter->hasParameter('map_security_identities'));
        static::assertFalse($this->filter->hasParameter('user_id'));
        static::assertFalse($this->filter->hasParameter('sharing_manager_enabled'));

        /** @var KernelEvent $event */
        $event = $this->getMockBuilder(KernelEvent::class)->disableOriginalConstructor()->getMock();
        $this->listener->onEvent($event);

        static::assertTrue($this->filter->hasParameter('has_security_identities'));
        static::assertTrue($this->filter->hasParameter('map_security_identities'));
        static::assertTrue($this->filter->hasParameter('user_id'));
        static::assertTrue($this->filter->hasParameter('sharing_manager_enabled'));

        static::assertFalse($this->filter->getParameter('has_security_identities'));
        static::assertSame([], $this->filter->getParameter('map_security_identities'));
        static::assertNull($this->filter->getParameter('user_id'));
        static::assertTrue($this->filter->getParameter('sharing_manager_enabled'));
    }

    public function testOnEvent(): void
    {
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $this->filterCollection->expects(static::once())
            ->method('getEnabledFilters')
            ->willReturn([
                'sharing' => $this->filter,
            ])
        ;

        $this->tokenStorage->expects(static::atLeastOnce())
            ->method('getToken')
            ->willReturn($token)
        ;

        $this->sidManager->expects(static::once())
            ->method('getSecurityIdentities')
            ->with($token)
            ->willReturn([
                new RoleSecurityIdentity('role', 'ROLE_USER'),
                new RoleSecurityIdentity('role', 'ROLE_ADMIN'),
            ])
        ;

        $this->sharingManager->expects(static::atLeastOnce())
            ->method('hasIdentityConfig')
            ->with(...['role'])
            ->willReturn(true)
        ;

        $this->sharingManager->expects(static::atLeastOnce())
            ->method('getIdentityConfig')
            ->willReturnCallback(function ($v) {
                $config = $this->getMockBuilder(SharingIdentityConfigInterface::class)->getMock();
                $config->expects($this->atLeastOnce())
                    ->method('getType')
                    ->willReturnCallback(static function () use ($v) {
                        return 'role' === $v
                            ? MockRole::class
                            : 'foo';
                    })
                ;

                return $config;
            })
        ;

        $user = new MockUserRoleable();

        $token->expects(static::atLeastOnce())
            ->method('getUser')
            ->willReturn($user)
        ;

        static::assertFalse($this->filter->hasParameter('has_security_identities'));
        static::assertFalse($this->filter->hasParameter('map_security_identities'));
        static::assertFalse($this->filter->hasParameter('user_id'));
        static::assertFalse($this->filter->hasParameter('sharing_manager_enabled'));

        /** @var KernelEvent $event */
        $event = $this->getMockBuilder(KernelEvent::class)->disableOriginalConstructor()->getMock();
        $this->listener->onEvent($event);

        static::assertTrue($this->filter->hasParameter('has_security_identities'));
        static::assertTrue($this->filter->hasParameter('map_security_identities'));
        static::assertTrue($this->filter->hasParameter('user_id'));
        static::assertTrue($this->filter->hasParameter('sharing_manager_enabled'));

        static::assertTrue($this->filter->getParameter('has_security_identities'));
        static::assertSame([
            MockRole::class => 'ROLE_USER, ROLE_ADMIN',
        ], $this->filter->getParameter('map_security_identities'));
        static::assertSame(50, $this->filter->getParameter('user_id'));
        static::assertTrue($this->filter->getParameter('sharing_manager_enabled'));
    }
}
