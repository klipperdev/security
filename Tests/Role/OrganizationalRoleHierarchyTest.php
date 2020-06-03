<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Role;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Query\FilterCollection;
use Doctrine\Persistence\ManagerRegistry as ManagerRegistryInterface;
use Doctrine\Persistence\ObjectRepository;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\RoleHierarchicalInterface;
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Klipper\Component\Security\Role\OrganizationalRoleHierarchy;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRole;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class OrganizationalRoleHierarchyTest extends TestCase
{
    /**
     * @var ManagerRegistryInterface|MockObject
     */
    protected $registry;

    protected ?string $roleClassname = null;

    /**
     * @var CacheItemPoolInterface|MockObject
     */
    protected $cache;

    /**
     * @var EventDispatcherInterface|MockObject
     */
    protected $eventDispatcher;

    /**
     * @var EntityManagerInterface|MockObject
     */
    protected $em;

    /**
     * @var MockObject|ObjectRepository
     */
    protected $repo;

    /**
     * @var FilterCollection|MockObject
     */
    protected $filters;

    /**
     * @var MockObject|OrganizationalContextInterface
     */
    protected $context;

    protected ?OrganizationalRoleHierarchy $roleHierarchy = null;

    protected function setUp(): void
    {
        $this->registry = $this->getMockBuilder(ManagerRegistryInterface::class)->getMock();
        $this->roleClassname = MockRole::class;
        $this->cache = $this->getMockBuilder(CacheItemPoolInterface::class)->getMock();
        $this->eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $this->context = $this->getMockBuilder(OrganizationalContextInterface::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->repo = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $this->filters = $this->getMockBuilder(FilterCollection::class)->disableOriginalConstructor()->getMock();

        $hierarchy = [
            'ROLE_ADMIN' => [
                'ROLE_USER',
            ],
        ];
        $this->roleHierarchy = new OrganizationalRoleHierarchy(
            $hierarchy,
            $this->registry,
            $this->cache,
            $this->context,
            $this->roleClassname
        );

        $this->roleHierarchy->setEventDispatcher($this->eventDispatcher);

        $this->registry->expects(static::any())
            ->method('getManagerForClass')
            ->with($this->roleClassname)
            ->willReturn($this->em)
        ;

        $this->em->expects(static::any())
            ->method('getRepository')
            ->with($this->roleClassname)
            ->willReturn($this->repo)
        ;

        $this->em->expects(static::any())
            ->method('getFilters')
            ->willReturn($this->filters)
        ;
    }

    public function getOrganizationContextType(): array
    {
        return [
            [null],
            ['user'],
            ['organization'],
        ];
    }

    /**
     * @dataProvider getOrganizationContextType
     *
     * @param null|string $orgContextType The organization context type
     *
     * @throws
     */
    public function testGetReachableRolesWithCustomRoles(?string $orgContextType): void
    {
        $this->initOrgContextType($orgContextType);

        $roles = [
            new MockRole('ROLE_ADMIN'),
        ];
        $validRoles = [
            'ROLE_ADMIN',
            'ROLE_USER',
        ];

        $cacheItem = $this->getMockBuilder(CacheItemInterface::class)->getMock();

        $this->cache->expects(static::once())
            ->method('getItem')
            ->willReturn($cacheItem)
        ;

        $cacheItem->expects(static::once())
            ->method('get')
            ->with()
            ->willReturn(null)
        ;

        $this->eventDispatcher->expects(static::atLeastOnce())
            ->method('dispatch')
        ;

        $sqlFilters = [
            'test_filter' => $this->getMockForAbstractClass(SQLFilter::class, [], '', false),
        ];

        $this->filters->expects(static::once())
            ->method('getEnabledFilters')
            ->willReturn($sqlFilters)
        ;

        $this->filters->expects(static::once())
            ->method('disable')
            ->with('test_filter')
        ;

        $dbRole = $this->getMockBuilder(RoleHierarchicalInterface::class)->getMock();
        $dbRoleChildren = $this->getMockBuilder(Collection::class)->getMock();

        $dbRole->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn('ROLE_ADMIN')
        ;

        $dbRole->expects(static::once())
            ->method('getChildren')
            ->willReturn($dbRoleChildren)
        ;

        $dbRoleChildren->expects(static::once())
            ->method('toArray')
            ->willReturn([])
        ;

        $this->repo->expects(static::once())
            ->method('findBy')
            ->with(['name' => ['ROLE_ADMIN']])
            ->willReturn([$dbRole])
        ;

        $this->filters->expects(static::once())
            ->method('enable')
            ->with('test_filter')
        ;

        $cacheItem->expects(static::once())
            ->method('set')
        ;

        $this->cache->expects(static::once())
            ->method('save')
        ;

        $fullRoles = $this->roleHierarchy->getReachableRoleNames($roles);

        static::assertCount(2, $fullRoles);
        static::assertEquals($validRoles, $fullRoles);
    }

    /**
     * Init the organization context type.
     *
     * @param null|string $orgContextType The organization context type
     */
    protected function initOrgContextType($orgContextType): void
    {
        $org = null;

        if (\in_array($orgContextType, ['user', 'organization'], true)) {
            $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
            $org->expects(static::once())
                ->method('isUserOrganization')
                ->willReturn('user' === $orgContextType)
            ;
        }

        $this->context->expects(static::once())
            ->method('getCurrentOrganization')
            ->willReturn($org)
        ;
    }
}
