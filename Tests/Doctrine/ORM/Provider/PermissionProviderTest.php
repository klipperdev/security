<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Doctrine\ORM\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Klipper\Component\Security\Doctrine\ORM\Provider\PermissionProvider;
use Klipper\Component\Security\Model\PermissionInterface;
use Klipper\Component\Security\Permission\FieldVote;
use Klipper\Component\Security\Permission\PermissionConfigInterface;
use Klipper\Component\Security\Permission\PermissionProviderInterface;
use Klipper\Component\Security\PermissionContexts;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObject;
use Klipper\Component\Security\Tests\Fixtures\Model\MockOrganization;
use Klipper\Component\Security\Tests\Fixtures\Model\MockOrganizationUser;
use Klipper\Component\Security\Tests\Fixtures\Model\MockPermission;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class PermissionProviderTest extends TestCase
{
    /**
     * @var EntityRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $permissionRepo;

    /**
     * @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registry;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|QueryBuilder
     */
    protected $qb;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Query
     */
    protected $query;

    /**
     * @throws
     */
    protected function setUp(): void
    {
        $this->permissionRepo = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $this->registry = $this->getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->getMock();
        $this->qb = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();

        $this->query = $this->getMockForAbstractClass(
            AbstractQuery::class,
            [],
            '',
            false,
            false,
            true,
            [
                'getResult',
            ]
        );
    }

    public function testGetPermissions(): void
    {
        $roles = [
            'ROLE_USER',
        ];
        $result = [
            new MockPermission(),
        ];

        $this->permissionRepo->expects(static::once())
            ->method('createQueryBuilder')
            ->with('p')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(0))
            ->method('leftJoin')
            ->with('p.roles', 'r')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(1))
            ->method('where')
            ->with('UPPER(r.name) IN (:roles)')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(2))
            ->method('setParameter')
            ->with('roles', $roles)
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(3))
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(4))
            ->method('addOrderBy')
            ->with('p.field', 'asc')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(5))
            ->method('addOrderBy')
            ->with('p.operation', 'asc')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(6))
            ->method('getQuery')
            ->willReturn($this->query)
        ;

        $this->query->expects(static::once())
            ->method('getResult')
            ->willReturn($result)
        ;

        $provider = $this->createProvider();
        static::assertSame($result, $provider->getPermissions($roles));
    }

    public function testGetPermissionsOptimizationWithEmptyRoles(): void
    {
        $this->permissionRepo->expects(static::never())
            ->method('createQueryBuilder')
        ;

        $provider = $this->createProvider();
        static::assertSame([], $provider->getPermissions([]));
    }

    public function testGetMasterClass(): void
    {
        $om = $this->getMockBuilder(ObjectManager::class)->getMock();

        /** @var PermissionConfigInterface|\PHPUnit\Framework\MockObject\MockObject $permConfig */
        $permConfig = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();

        $permConfig->expects(static::once())
            ->method('getType')
            ->willReturn(MockOrganizationUser::class)
        ;

        $permConfig->expects(static::atLeast(2))
            ->method('getMaster')
            ->willReturn('organization')
        ;

        $this->registry->expects(static::once())
            ->method('getManagerForClass')
            ->willReturn($om)
        ;

        $meta = $this->getMockBuilder(ClassMetadata::class)->getMock();

        $meta->expects(static::once())
            ->method('getAssociationTargetClass')
            ->with('organization')
            ->willReturn(MockOrganization::class)
        ;

        $om->expects(static::once())
            ->method('getClassMetadata')
            ->with(MockOrganizationUser::class)
            ->willReturn($meta)
        ;

        $provider = $this->createProvider();
        static::assertSame(MockOrganization::class, $provider->getMasterClass($permConfig));
    }

    public function testGetMasterClassWithoutObjectManagerForClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The doctrine object manager is not found for the class "Klipper\\Component\\Security\\Tests\\Fixtures\\Model\\MockObject"');

        /** @var PermissionConfigInterface|\PHPUnit\Framework\MockObject\MockObject $permConfig */
        $permConfig = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();

        $permConfig->expects(static::atLeast(2))
            ->method('getType')
            ->willReturn(MockObject::class)
        ;

        $provider = $this->createProvider();

        $this->registry->expects(static::once())
            ->method('getManagers')
            ->willReturn([])
        ;

        $provider->getMasterClass($permConfig);
    }

    public function testGetMasterClassWithoutMaster(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The permission master association is not configured for the class "Klipper\\Component\\Security\\Tests\\Fixtures\\Model\\MockObject"');

        $om = $this->getMockBuilder(ObjectManager::class)->getMock();

        /** @var PermissionConfigInterface|\PHPUnit\Framework\MockObject\MockObject $permConfig */
        $permConfig = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();

        $permConfig->expects(static::atLeast(2))
            ->method('getType')
            ->willReturn(MockObject::class)
        ;

        $permConfig->expects(static::once())
            ->method('getMaster')
            ->willReturn(null)
        ;

        $this->registry->expects(static::once())
            ->method('getManagerForClass')
            ->willReturn($om)
        ;

        $provider = $this->createProvider();
        $provider->getMasterClass($permConfig);
    }

    public function testGetPermissionsBySubject(): void
    {
        $subject = new FieldVote(MockObject::class, 'name');
        $expected = [
            new MockPermission(),
        ];

        $this->permissionRepo->expects(static::once())
            ->method('createQueryBuilder')
            ->with('p')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(0))
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(1))
            ->method('addOrderBy')
            ->with('p.field', 'asc')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(2))
            ->method('addOrderBy')
            ->with('p.operation', 'asc')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(3))
            ->method('andWhere')
            ->with('p.class = :class')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(4))
            ->method('setParameter')
            ->with('class', $subject->getSubject()->getType())
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(5))
            ->method('andWhere')
            ->with('p.field = :field')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(6))
            ->method('setParameter')
            ->with('field', $subject->getField())
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(7))
            ->method('getQuery')
            ->willReturn($this->query)
        ;

        $this->query->expects(static::once())
            ->method('getResult')
            ->willReturn($expected)
        ;

        $provider = $this->createProvider();
        $res = $provider->getPermissionsBySubject($subject);

        static::assertSame($expected, $res);
    }

    public function testGetPermissionsBySubjectAndContexts(): void
    {
        $subject = new FieldVote(MockObject::class, 'name');
        $expected = [
            new MockPermission(),
        ];

        $this->permissionRepo->expects(static::once())
            ->method('createQueryBuilder')
            ->with('p')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(0))
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(1))
            ->method('addOrderBy')
            ->with('p.field', 'asc')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(2))
            ->method('addOrderBy')
            ->with('p.operation', 'asc')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(3))
            ->method('setParameter')
            ->with('context_role', '%"'.PermissionContexts::ROLE.'"%')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(4))
            ->method('setParameter')
            ->with('context_organization_role', '%"'.PermissionContexts::ORGANIZATION_ROLE.'"%')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(5))
            ->method('setParameter')
            ->with('context_sharing', '%"'.PermissionContexts::SHARING.'"%')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(6))
            ->method('andWhere')
            ->with('p.contexts IS NULL OR p.contexts LIKE :context_role OR p.contexts LIKE :context_organization_role OR p.contexts LIKE :context_sharing')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(7))
            ->method('andWhere')
            ->with('p.class = :class')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(8))
            ->method('setParameter')
            ->with('class', $subject->getSubject()->getType())
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(9))
            ->method('andWhere')
            ->with('p.field = :field')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(10))
            ->method('setParameter')
            ->with('field', $subject->getField())
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(11))
            ->method('getQuery')
            ->willReturn($this->query)
        ;

        $this->query->expects(static::once())
            ->method('getResult')
            ->willReturn($expected)
        ;

        $provider = $this->createProvider();
        $res = $provider->getPermissionsBySubject($subject, [
            PermissionContexts::ROLE,
            PermissionContexts::ORGANIZATION_ROLE,
            PermissionContexts::SHARING,
        ]);

        static::assertSame($expected, $res);
    }

    public function testGetPermissionsBySubjectWithoutSubject(): void
    {
        $subject = null;
        $expected = [
            new MockPermission(),
        ];

        $this->permissionRepo->expects(static::once())
            ->method('createQueryBuilder')
            ->with('p')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(0))
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(1))
            ->method('addOrderBy')
            ->with('p.field', 'asc')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(2))
            ->method('addOrderBy')
            ->with('p.operation', 'asc')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(3))
            ->method('andWhere')
            ->with('p.class IS NULL')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(4))
            ->method('andWhere')
            ->with('p.field IS NULL')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(5))
            ->method('getQuery')
            ->willReturn($this->query)
        ;

        $this->query->expects(static::once())
            ->method('getResult')
            ->willReturn($expected)
        ;

        $provider = $this->createProvider();
        $res = $provider->getPermissionsBySubject($subject);

        static::assertSame($expected, $res);
    }

    public function testGetConfigPermissions(): void
    {
        $expected = [
            new MockPermission(),
        ];

        $this->permissionRepo->expects(static::once())
            ->method('createQueryBuilder')
            ->with('p')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(0))
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(1))
            ->method('addOrderBy')
            ->with('p.field', 'asc')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(2))
            ->method('addOrderBy')
            ->with('p.operation', 'asc')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(3))
            ->method('andWhere')
            ->with('p.class = :class')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(4))
            ->method('setParameter')
            ->with('class', PermissionProviderInterface::CONFIG_CLASS)
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::at(5))
            ->method('getQuery')
            ->willReturn($this->query)
        ;

        $this->query->expects(static::once())
            ->method('getResult')
            ->willReturn($expected)
        ;

        $provider = $this->createProvider();
        $res = $provider->getConfigPermissions();

        static::assertSame($expected, $res);
    }

    protected function createProvider(): PermissionProvider
    {
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();

        $this->registry->expects(static::any())
            ->method('getManagerForClass')
            ->willReturnCallback(static function ($class) use ($em) {
                return PermissionInterface::class === $class ? $em : null;
            })
        ;

        $em->expects(static::any())
            ->method('getRepository')
            ->willReturnCallback(function ($class) {
                return PermissionInterface::class === $class ? $this->permissionRepo : null;
            })
        ;

        return new PermissionProvider(
            $this->registry
        );
    }
}
