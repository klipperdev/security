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

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
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
     * @var EntityRepository|MockObject
     */
    protected $permissionRepo;

    /**
     * @var ManagerRegistry|MockObject
     */
    protected $registry;

    /**
     * @var MockObject|QueryBuilder
     */
    protected $qb;

    /**
     * @var MockObject|Query
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

        $this->qb->expects(static::once())
            ->method('leftJoin')
            ->with('p.roles', 'r')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('where')
            ->with('UPPER(r.name) IN (:roles)')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('setParameter')
            ->with('roles', $roles)
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::exactly(2))
            ->method('addOrderBy')
            ->willReturnMap([
                ['p.field', 'asc', $this->qb],
                ['p.operation', 'asc', $this->qb],
            ])
        ;

        $this->qb->expects(static::once())
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

        /** @var MockObject|PermissionConfigInterface $permConfig */
        $permConfig = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();

        $permConfig->expects(static::once())
            ->method('getType')
            ->willReturn(MockOrganizationUser::class)
        ;

        $permConfig->expects(static::exactly(2))
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

        /** @var MockObject|PermissionConfigInterface $permConfig */
        $permConfig = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();

        $permConfig->expects(static::exactly(2))
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

        /** @var MockObject|PermissionConfigInterface $permConfig */
        $permConfig = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();

        $permConfig->expects(static::exactly(2))
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

        $this->qb->expects(static::once())
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::exactly(2))
            ->method('addOrderBy')
            ->willReturnMap([
                ['p.field', 'asc', $this->qb],
                ['p.operation', 'asc', $this->qb],
            ])
        ;

        $this->qb->expects(static::exactly(2))
            ->method('andWhere')
            ->willReturnMap([
                ['p.class = :class', $this->qb],
                ['p.field = :field', $this->qb],
            ])
        ;

        $this->qb->expects(static::exactly(2))
            ->method('setParameter')
            ->willReturnMap([
                ['class', $subject->getSubject()->getType(), null, $this->qb],
                ['field', $subject->getField(), null, $this->qb],
            ])
        ;

        $this->qb->expects(static::once())
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

        $this->qb->expects(static::once())
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::exactly(2))
            ->method('addOrderBy')
            ->willReturnMap([
                ['p.field', 'asc', $this->qb],
                ['p.operation', 'asc', $this->qb],
            ])
        ;

        $this->qb->expects(static::exactly(5))
            ->method('setParameter')
            ->willReturnMap([
                ['context_role', '%"'.PermissionContexts::ROLE.'"%', null, $this->qb],
                ['context_organization_role', '%"'.PermissionContexts::ORGANIZATION_ROLE.'"%', null, $this->qb],
                ['context_sharing', '%"'.PermissionContexts::SHARING.'"%', null, $this->qb],
                ['class', $subject->getSubject()->getType(), null, $this->qb],
                ['field', $subject->getField(), null, $this->qb],
            ])
        ;

        $this->qb->expects(static::exactly(3))
            ->method('andWhere')
            ->willReturnMap([
                ['p.contexts IS NULL OR p.contexts LIKE :context_role OR p.contexts LIKE :context_organization_role OR p.contexts LIKE :context_sharing', $this->qb],
                ['p.class = :class', $this->qb],
                ['p.field = :field', $this->qb],
            ])
        ;

        $this->qb->expects(static::once())
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

        $this->qb->expects(static::once())
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::exactly(2))
            ->method('addOrderBy')
            ->willReturnMap([
                ['p.field', 'asc', $this->qb],
                ['p.operation', 'asc', $this->qb],
            ])
        ;

        $this->qb->expects(static::exactly(2))
            ->method('andWhere')
            ->willReturnMap([
                ['p.class IS NULL', $this->qb],
                ['p.field IS NULL', $this->qb],
            ])
        ;

        $this->qb->expects(static::once())
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

        $this->qb->expects(static::once())
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::exactly(2))
            ->method('addOrderBy')
            ->willReturnMap([
                ['p.field', 'asc', $this->qb],
                ['p.operation', 'asc', $this->qb],
            ])
        ;

        $this->qb->expects(static::once())
            ->method('andWhere')
            ->with('p.class = :class')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('setParameter')
            ->with('class', PermissionProviderInterface::CONFIG_CLASS)
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
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
