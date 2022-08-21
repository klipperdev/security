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
use Klipper\Component\Security\Doctrine\ORM\Provider\SharingProvider;
use Klipper\Component\Security\Exception\InvalidArgumentException;
use Klipper\Component\Security\Identity\RoleSecurityIdentity;
use Klipper\Component\Security\Identity\SecurityIdentityManagerInterface;
use Klipper\Component\Security\Identity\SubjectIdentity;
use Klipper\Component\Security\Identity\UserSecurityIdentity;
use Klipper\Component\Security\Model\RoleInterface;
use Klipper\Component\Security\Model\SharingInterface;
use Klipper\Component\Security\Sharing\SharingIdentityConfig;
use Klipper\Component\Security\Sharing\SharingManagerInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObject;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRole;
use Klipper\Component\Security\Tests\Fixtures\Model\MockSharing;
use Klipper\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SharingProviderTest extends TestCase
{
    /**
     * @var EntityRepository|MockObject
     */
    protected $roleRepo;

    /**
     * @var EntityRepository|MockObject
     */
    protected $sharingRepo;

    /**
     * @var MockObject|SecurityIdentityManagerInterface
     */
    protected $sidManager;

    /**
     * @var MockObject|TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var MockObject|SharingManagerInterface
     */
    protected $sharingManager;

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
        $this->roleRepo = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $this->sharingRepo = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $this->sidManager = $this->getMockBuilder(SecurityIdentityManagerInterface::class)->getMock();
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
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
                'execute',
            ]
        );
    }

    public function testGetPermissionRoles(): void
    {
        $roles = [
            'ROLE_USER',
        ];
        $result = [
            new MockRole('ROLE_USER'),
        ];

        $this->roleRepo->expects(static::once())
            ->method('createQueryBuilder')
            ->with('r')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('addSelect')
            ->with('p')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('leftJoin')
            ->with('r.permissions', 'p')
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
        static::assertSame($result, $provider->getPermissionRoles($roles));
    }

    public function testGetPermissionRolesWithEmptyRoles(): void
    {
        $this->roleRepo->expects(static::never())
            ->method('createQueryBuilder')
        ;

        $provider = $this->createProvider();
        static::assertSame([], $provider->getPermissionRoles([]));
    }

    public function testGetSharingEntries(): void
    {
        $subjects = [
            SubjectIdentity::fromObject(new MockObject('foo', 42)),
            SubjectIdentity::fromObject(new MockObject('bar', 23)),
        ];
        $result = [];

        $this->sharingRepo->expects(static::once())
            ->method('createQueryBuilder')
            ->with('s')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('addSelect')
            ->with('p')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('leftJoin')
            ->with('s.permissions', 'p')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('where')
            ->with('(s.subjectClass = :subject0_class AND s.subjectId = :subject0_id) OR (s.subjectClass = :subject1_class AND s.subjectId = :subject1_id)')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::exactly(4))
            ->method('setParameter')
            ->willReturnMap([
                ['subject0_class', MockObject::class, null, $this->qb],
                ['subject0_id', 42, null, $this->qb],
                ['subject1_class', MockObject::class, null, $this->qb],
                ['subject1_id', 23, null, $this->qb],
            ])
        ;

        $this->qb->expects(static::once())
            ->method('andWhere')
            ->with('s.enabled = TRUE AND (s.startedAt IS NULL OR s.startedAt <= CURRENT_TIMESTAMP()) AND (s.endedAt IS NULL OR s.endedAt >= CURRENT_TIMESTAMP())')
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
        static::assertSame($result, $provider->getSharingEntries($subjects));
    }

    public function testGetSharingEntriesWithEmptySubjects(): void
    {
        $this->sharingRepo->expects(static::never())
            ->method('createQueryBuilder')
        ;

        $provider = $this->createProvider();
        static::assertSame([], $provider->getSharingEntries([]));
    }

    public function testGetPermissionRolesWithSecurityIdentities(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
            new UserSecurityIdentity(MockUserRoleable::class, 'user.test'),
        ];
        $subjects = [
            SubjectIdentity::fromObject(new MockObject('foo', 42)),
            SubjectIdentity::fromObject(new MockObject('bar', 23)),
        ];
        $result = [];

        $this->sharingManager->expects(static::exactly(2))
            ->method('getIdentityConfig')
            ->willReturnMap([
                [MockRole::class, new SharingIdentityConfig(MockRole::class, 'role')],
                [MockUserRoleable::class, new SharingIdentityConfig(MockUserRoleable::class, 'role')],
            ])
        ;

        $this->sharingRepo->expects(static::once())
            ->method('createQueryBuilder')
            ->with('s')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('addSelect')
            ->with('p')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('leftJoin')
            ->with('s.permissions', 'p')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('where')
            ->with('(s.subjectClass = :subject0_class AND s.subjectId = :subject0_id) OR (s.subjectClass = :subject1_class AND s.subjectId = :subject1_id)')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::exactly(8))
            ->method('setParameter')
            ->willReturnMap([
                ['subject0_class', MockObject::class, null, $this->qb],
                ['subject0_id', 42, null, $this->qb],
                ['subject1_class', MockObject::class, null, $this->qb],
                ['subject1_id', 23, null, $this->qb],
                ['sid0_class', MockRole::class, null, $this->qb],
                ['sid0_ids', ['ROLE_USER'], null, $this->qb],
                ['sid1_class', MockUserRoleable::class, null, $this->qb],
                ['sid1_ids', ['user.test'], null, $this->qb],
            ])
        ;

        $this->qb->expects(static::exactly(2))
            ->method('andWhere')
            ->willReturnMap([
                ['(s.identityClass = :sid0_class AND s.identityName IN (:sid0_ids)) OR (s.identityClass = :sid1_class AND s.identityName IN (:sid1_ids))', $this->qb],
                ['s.enabled = TRUE AND (s.startedAt IS NULL OR s.startedAt <= CURRENT_TIMESTAMP()) AND (s.endedAt IS NULL OR s.endedAt >= CURRENT_TIMESTAMP())', $this->qb],
            ])
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
        static::assertSame($result, $provider->getSharingEntries($subjects, $sids));
    }

    public function testGetPermissionRolesWithEmptySecurityIdentities(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, AuthenticatedVoter::PUBLIC_ACCESS),
        ];
        $subjects = [
            SubjectIdentity::fromObject(new MockObject('foo', 42)),
            SubjectIdentity::fromObject(new MockObject('bar', 23)),
        ];
        $result = [];

        $this->sharingManager->expects(static::never())
            ->method('getIdentityConfig')
        ;

        $this->sharingRepo->expects(static::once())
            ->method('createQueryBuilder')
            ->with('s')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('addSelect')
            ->with('p')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('leftJoin')
            ->with('s.permissions', 'p')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('where')
            ->with('(s.subjectClass = :subject0_class AND s.subjectId = :subject0_id) OR (s.subjectClass = :subject1_class AND s.subjectId = :subject1_id)')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::exactly(4))
            ->method('setParameter')
            ->willReturnMap([
                ['subject0_class', MockObject::class, null, $this->qb],
                ['subject0_id', 42, null, $this->qb],
                ['subject1_class', MockObject::class, null, $this->qb],
                ['subject1_id', 23, null, $this->qb],
            ])
        ;

        $this->qb->expects(static::once())
            ->method('andWhere')
            ->with('s.enabled = TRUE AND (s.startedAt IS NULL OR s.startedAt <= CURRENT_TIMESTAMP()) AND (s.endedAt IS NULL OR s.endedAt >= CURRENT_TIMESTAMP())')
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
        static::assertSame($result, $provider->getSharingEntries($subjects, $sids));
    }

    public function testGetSharingEntriesWithoutSharingManager(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "setSharingManager()" must be called before');

        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
            new UserSecurityIdentity(MockUserRoleable::class, 'user.test'),
        ];
        $subjects = [
            SubjectIdentity::fromObject(new MockObject('foo', 42)),
            SubjectIdentity::fromObject(new MockObject('bar', 23)),
        ];

        $this->sharingRepo->expects(static::once())
            ->method('createQueryBuilder')
            ->with('s')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('addSelect')
            ->with('p')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('leftJoin')
            ->with('s.permissions', 'p')
            ->willReturn($this->qb)
        ;

        $provider = $this->createProvider(MockRole::class, MockSharing::class, false);
        $provider->getSharingEntries($subjects, $sids);
    }

    public function testRenameIdentity(): void
    {
        $this->sharingRepo->expects(static::once())
            ->method('createQueryBuilder')
            ->with('s')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('update')
            ->with(MockSharing::class, 's')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('set')
            ->with('s.identityName', ':newName')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('where')
            ->with('s.identityClass = :type')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('andWhere')
            ->with('s.identityName = :oldName')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::exactly(3))
            ->method('setParameter')
            ->willReturnMap([
                ['type', MockRole::class, null, $this->qb],
                ['oldName', 'ROLE_FOO', null, $this->qb],
                ['newName', 'ROLE_BAR', null, $this->qb],
            ])
        ;

        $this->qb->expects(static::once())
            ->method('getQuery')
            ->willReturn($this->query)
        ;

        $this->query->expects(static::once())
            ->method('execute')
            ->willReturn('RESULT')
        ;

        $provider = $this->createProvider();
        $provider->renameIdentity(MockRole::class, 'ROLE_FOO', 'ROLE_BAR');
    }

    public function testDeleteIdentity(): void
    {
        $this->sharingRepo->expects(static::once())
            ->method('createQueryBuilder')
            ->with('s')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('delete')
            ->with(MockSharing::class, 's')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('where')
            ->with('s.identityClass = :type')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('andWhere')
            ->with('s.identityName = :name')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::exactly(2))
            ->method('setParameter')
            ->willReturnMap([
                ['type', MockRole::class, null, $this->qb],
                ['name', 'ROLE_FOO', null, $this->qb],
            ])
        ;

        $this->qb->expects(static::once())
            ->method('getQuery')
            ->willReturn($this->query)
        ;

        $this->query->expects(static::once())
            ->method('execute')
            ->willReturn('RESULT')
        ;

        $provider = $this->createProvider();
        $provider->deleteIdentity(MockRole::class, 'ROLE_FOO');
    }

    public function testDeletes(): void
    {
        $ids = [42, 50];

        $this->sharingRepo->expects(static::once())
            ->method('createQueryBuilder')
            ->with('s')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('delete')
            ->with(MockSharing::class, 's')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('where')
            ->with('s.id IN (:ids)')
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('setParameter')
            ->with('ids', $ids)
            ->willReturn($this->qb)
        ;

        $this->qb->expects(static::once())
            ->method('getQuery')
            ->willReturn($this->query)
        ;

        $this->query->expects(static::once())
            ->method('execute')
            ->willReturn('RESULT')
        ;

        $provider = $this->createProvider();
        $provider->deletes($ids);
    }

    protected function createProvider(string $roleClass = MockRole::class, string $sharingClass = MockSharing::class, bool $addManager = true): SharingProvider
    {
        /** @var ManagerRegistry|MockObject $registry */
        $registry = $this->getMockBuilder(ManagerRegistry::class)->getMock();

        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();

        $this->roleRepo->expects(static::any())
            ->method('getClassName')
            ->willReturn($roleClass)
        ;

        $this->sharingRepo->expects(static::any())
            ->method('getClassName')
            ->willReturn($sharingClass)
        ;

        $registry->expects(static::any())
            ->method('getManagerForClass')
            ->willReturnCallback(static function ($class) use ($em) {
                return \in_array($class, [RoleInterface::class, SharingInterface::class], true) ? $em : null;
            })
        ;

        $em->expects(static::any())
            ->method('getRepository')
            ->willReturnCallback(function ($class) {
                $repo = null;

                if (RoleInterface::class === $class) {
                    $repo = $this->roleRepo;
                } elseif (SharingInterface::class === $class) {
                    $repo = $this->sharingRepo;
                }

                return $repo;
            })
        ;

        $provider = new SharingProvider(
            $registry,
            $this->sidManager,
            $this->tokenStorage
        );

        if ($addManager) {
            $provider->setSharingManager($this->sharingManager);
        }

        return $provider;
    }
}
