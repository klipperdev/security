<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Listener;

use Klipper\Component\Security\Event\AddSecurityIdentityEvent;
use Klipper\Component\Security\Listener\OrganizationSecurityIdentitySubscriber;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class OrganizationSecurityIdentitySubscriberTest extends TestCase
{
    /**
     * @var MockObject|RoleHierarchyInterface
     */
    protected $roleHierarchy;

    /**
     * @var MockObject|OrganizationalContextInterface
     */
    protected $orgContext;

    protected ?OrganizationSecurityIdentitySubscriber $listener = null;

    protected function setUp(): void
    {
        $this->roleHierarchy = $this->getMockBuilder(RoleHierarchyInterface::class)->getMock();
        $this->orgContext = $this->getMockBuilder(OrganizationalContextInterface::class)->getMock();
        $this->listener = new OrganizationSecurityIdentitySubscriber($this->roleHierarchy, $this->orgContext);

        static::assertCount(1, OrganizationSecurityIdentitySubscriber::getSubscribedEvents());
    }

    public function testCacheIdWithPersonalOrganization(): void
    {
        $this->orgContext->expects(static::once())
            ->method('getCurrentOrganization')
            ->willReturn(null)
        ;

        static::assertSame('', $this->listener->getCacheId());
    }

    public function testCacheIdWithOrganization(): void
    {
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        $org->expects(static::once())
            ->method('getId')
            ->willReturn(42)
        ;

        $this->orgContext->expects(static::once())
            ->method('getCurrentOrganization')
            ->willReturn($org)
        ;

        static::assertSame('org42', $this->listener->getCacheId());
    }

    public function testAddOrganizationSecurityIdentities(): void
    {
        /** @var MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $sids = [];
        $event = new AddSecurityIdentityEvent($token, $sids);

        $this->listener->addOrganizationSecurityIdentities($event);

        static::assertSame($sids, $event->getSecurityIdentities());
    }

    public function testAddOrganizationSecurityIdentitiesWithInvalidArgument(): void
    {
        /** @var MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $sids = [];
        $event = new AddSecurityIdentityEvent($token, $sids);

        $token->expects(static::once())
            ->method('getUser')
            ->willThrowException(new \InvalidArgumentException('Test'))
        ;

        $this->listener->addOrganizationSecurityIdentities($event);

        static::assertSame($sids, $event->getSecurityIdentities());
    }
}
