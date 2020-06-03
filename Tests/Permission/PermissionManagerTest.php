<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Permission;

use Klipper\Component\Security\Event\CheckPermissionEvent;
use Klipper\Component\Security\Event\PostLoadPermissionsEvent;
use Klipper\Component\Security\Event\PreLoadPermissionsEvent;
use Klipper\Component\Security\Identity\RoleSecurityIdentity;
use Klipper\Component\Security\Identity\SubjectIdentity;
use Klipper\Component\Security\Identity\UserSecurityIdentity;
use Klipper\Component\Security\Model\PermissionChecking;
use Klipper\Component\Security\Permission\FieldVote;
use Klipper\Component\Security\Permission\Loader\ConfigurationLoader;
use Klipper\Component\Security\Permission\PermissionConfig;
use Klipper\Component\Security\Permission\PermissionFactory;
use Klipper\Component\Security\Permission\PermissionFieldConfig;
use Klipper\Component\Security\Permission\PermissionManager;
use Klipper\Component\Security\Permission\PermissionProviderInterface;
use Klipper\Component\Security\Sharing\SharingManagerInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObject;
use Klipper\Component\Security\Tests\Fixtures\Model\MockOrganization;
use Klipper\Component\Security\Tests\Fixtures\Model\MockOrganizationUser;
use Klipper\Component\Security\Tests\Fixtures\Model\MockOrgOptionalRole;
use Klipper\Component\Security\Tests\Fixtures\Model\MockOrgRequiredRole;
use Klipper\Component\Security\Tests\Fixtures\Model\MockPermission;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRole;
use Klipper\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class PermissionManagerTest extends TestCase
{
    protected ?EventDispatcher $dispatcher = null;

    /**
     * @var MockObject|PermissionProviderInterface
     */
    protected $provider;

    protected ?PropertyAccessor $propertyAccessor = null;

    protected ?PermissionManager $pm = null;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
        $this->provider = $this->getMockBuilder(PermissionProviderInterface::class)->getMock();
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor
        );
    }

    public function testIsEnabled(): void
    {
        static::assertTrue($this->pm->isEnabled());

        $this->pm->setEnabled(false);
        static::assertFalse($this->pm->isEnabled());

        $this->pm->setEnabled(true);
        static::assertTrue($this->pm->isEnabled());
    }

    public function testSetEnabledWithSharingManager(): void
    {
        /** @var MockObject|SharingManagerInterface $sm */
        $sm = $this->getMockBuilder(SharingManagerInterface::class)->getMock();

        $this->pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor,
            null,
            $sm
        );

        $sm->expects(static::once())
            ->method('setEnabled')
            ->with(false)
        ;

        $this->pm->setEnabled(false);
    }

    public function testHasConfig(): void
    {
        $pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor,
            new PermissionFactory(new ConfigurationLoader([
                new PermissionConfig(MockObject::class),
            ]), 'resource')
        );

        static::assertTrue($pm->hasConfig(MockObject::class));
    }

    public function testHasNotConfig(): void
    {
        static::assertFalse($this->pm->hasConfig(MockObject::class));
    }

    public function testAddConfig(): void
    {
        static::assertFalse($this->pm->hasConfig(MockObject::class));

        $this->pm->addConfig(new PermissionConfig(MockObject::class));

        static::assertTrue($this->pm->hasConfig(MockObject::class));
    }

    public function testGetConfig(): void
    {
        $config = new PermissionConfig(MockObject::class);
        $this->pm->addConfig($config);

        static::assertTrue($this->pm->hasConfig(MockObject::class));
        static::assertSame($config, $this->pm->getConfig(MockObject::class));
    }

    public function testGetConfigWithNotManagedClass(): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\PermissionConfigNotFoundException::class);
        $this->expectExceptionMessage('The permission configuration for the class "Klipper\\Component\\Security\\Tests\\Fixtures\\Model\\MockObject" is not found');

        $this->pm->getConfig(MockObject::class);
    }

    public function testGetConfigs(): void
    {
        $expected = [
            MockObject::class => new PermissionConfig(MockObject::class),
        ];

        $this->pm->addConfig($expected[MockObject::class]);

        static::assertSame($expected, $this->pm->getConfigs());
    }

    public function testIsManaged(): void
    {
        $this->pm->addConfig(new PermissionConfig(MockObject::class));
        $object = new MockObject('foo');

        static::assertTrue($this->pm->isManaged($object));
    }

    public function testIsManagedWithInvalidSubject(): void
    {
        $object = new \stdClass();

        static::assertFalse($this->pm->isManaged($object));
    }

    public function testIsManagedWithNonExistentSubject(): void
    {
        static::assertFalse($this->pm->isManaged('FooBar'));
    }

    public function testIsManagedWithUnexpectedTypeException(): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "FieldVote|SubjectIdentityInterface|object|string", "NULL"');

        static::assertFalse($this->pm->isManaged(null));
    }

    public function testIsManagedWithNonManagedClass(): void
    {
        static::assertFalse($this->pm->isManaged(MockObject::class));
    }

    public function testIsFieldManaged(): void
    {
        $this->pm->addConfig(new PermissionConfig(MockObject::class, [], [], [
            new PermissionFieldConfig('name'),
        ]));

        $object = new MockObject('foo');
        $field = 'name';

        static::assertTrue($this->pm->isFieldManaged($object, $field));
    }

    public function testIsGranted(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = MockObject::class;
        $permission = 'view';

        static::assertTrue($this->pm->isGranted($sids, $permission, $object));
    }

    public function testIsGrantedWithNonExistentSubject(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = 'FooBar';
        $permission = 'view';

        static::assertFalse($this->pm->isGranted($sids, $permission, $object));
    }

    public function testIsGrantedWithGlobalPermission(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = null;
        $permission = 'foo';
        $perm = new MockPermission();
        $perm->setOperation('foo');

        $this->provider->expects(static::once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([$perm])
        ;

        static::assertTrue($this->pm->isGranted($sids, $permission, $object));
        $this->pm->clear();
    }

    public function testIsGrantedWithGlobalPermissionAndMaster(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
            new UserSecurityIdentity(MockUserRoleable::class, 'user.test'),
        ];
        $org = new MockOrganization('foo');
        $user = new MockUserRoleable();
        $orgUser = new MockOrganizationUser($org, $user);
        $permission = 'view';
        $perm = new MockPermission();
        $perm->setClass(MockOrganization::class);
        $perm->setOperation('view');

        $this->provider->expects(static::once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([$perm])
        ;

        $this->pm->addConfig(new PermissionConfig(MockOrganization::class));
        $this->pm->addConfig(new PermissionConfig(MockOrganizationUser::class, [], [], [], 'organization'));

        static::assertTrue($this->pm->isGranted($sids, $permission, $orgUser));
        $this->pm->clear();
    }

    public function testIsGrantedWithGlobalPermissionAndMasterWithEmptyObjectOfSubject(): void
    {
        $permConfigOrg = new PermissionConfig(MockOrganization::class);
        $permConfigOrgUser = new PermissionConfig(MockOrganizationUser::class, [], [], [], 'organization');

        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
            new UserSecurityIdentity(MockUserRoleable::class, 'user.test'),
        ];
        $object = new SubjectIdentity(MockOrganizationUser::class, 42);
        $permission = 'view';
        $perm = new MockPermission();
        $perm->setClass(MockOrganization::class);
        $perm->setOperation('view');

        $this->provider->expects(static::once())
            ->method('getMasterClass')
            ->with($permConfigOrgUser)
            ->willReturn(MockOrganization::class)
        ;

        $this->provider->expects(static::once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([$perm])
        ;

        $this->pm->addConfig($permConfigOrg);
        $this->pm->addConfig($permConfigOrgUser);

        $res = $this->pm->isGranted($sids, $permission, $object);
        static::assertTrue($res);
    }

    public function testIsGrantedWithGlobalPermissionWithoutGrant(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER__foo'),
            new RoleSecurityIdentity(MockRole::class, 'ROLE_ADMIN__foo'),
        ];
        $object = null;
        $permission = 'bar';
        $perm = new MockPermission();
        $perm->setOperation('baz');

        $this->provider->expects(static::once())
            ->method('getPermissions')
            ->with(['ROLE_USER', 'ROLE_ADMIN'])
            ->willReturn([$perm])
        ;

        static::assertFalse($this->pm->isGranted($sids, $permission, $object));
        $this->pm->clear();
    }

    public function testIsFieldGranted(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = new MockObject('foo');
        $field = 'name';
        $permission = 'view';

        static::assertTrue($this->pm->isFieldGranted($sids, $permission, $object, $field));
    }

    public function testIsGrantedWithSharingPermission(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = new MockObject('foo');
        $permission = 'test';

        $this->provider->expects(static::once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([])
        ;

        /** @var MockObject|SharingManagerInterface $sharingManager */
        $sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $sharingManager->expects(static::once())
            ->method('preloadRolePermissions')
            ->with([SubjectIdentity::fromObject($object)])
        ;

        $sharingManager->expects(static::once())
            ->method('isGranted')
            ->with($permission, SubjectIdentity::fromObject($object))
            ->willReturn(true)
        ;

        $this->pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor,
            null,
            $sharingManager
        );
        $this->pm->addConfig(new PermissionConfig(MockObject::class));

        static::assertTrue($this->pm->isGranted($sids, $permission, $object));
        $this->pm->clear();
    }

    public function testIsGrantedWithSystemPermission(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
            new UserSecurityIdentity(MockUserRoleable::class, 'user.test'),
        ];
        $org = new MockOrganization('foo');
        $user = new MockUserRoleable();
        $orgUser = new MockOrganizationUser($org, $user);

        $this->provider->expects(static::once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([])
        ;

        $this->pm->addConfig(new PermissionConfig(
            MockOrganization::class,
            [
                'view',
                'create',
                'update',
            ],
            [],
            [
                new PermissionFieldConfig('name', ['read']),
            ]
        ));
        $this->pm->addConfig(new PermissionConfig(
            MockOrganizationUser::class,
            [],
            [],
            [
                new PermissionFieldConfig('organization', ['edit']),
            ],
            'organization',
            [
                'create' => 'edit',
                'update' => 'edit',
            ]
        ));

        static::assertTrue($this->pm->isGranted($sids, 'view', $org));
        static::assertTrue($this->pm->isGranted($sids, 'view', $orgUser));
        static::assertTrue($this->pm->isFieldGranted($sids, 'read', $org, 'name'));
        static::assertFalse($this->pm->isFieldGranted($sids, 'edit', $org, 'name'));
        static::assertFalse($this->pm->isFieldGranted($sids, 'read', $orgUser, 'organization'));
        static::assertTrue($this->pm->isFieldGranted($sids, 'edit', $orgUser, 'organization'));
        $this->pm->clear();
    }

    public function getRoles(): array
    {
        return [
            [new MockRole('ROLE_TEST')],
            [new MockOrgOptionalRole('ROLE_TEST')],
            [new MockOrgRequiredRole('ROLE_TEST')],
        ];
    }

    /**
     * @dataProvider getRoles
     */
    public function testGetRolePermissions(MockRole $role): void
    {
        $subject = null;
        $permission = new MockPermission();
        $permission->setOperation('test');
        $permissions = [
            $permission,
        ];
        $expected = [
            new PermissionChecking($permissions[0], false),
        ];

        $this->provider->expects(static::once())
            ->method('getPermissions')
            ->with(['ROLE_TEST'])
            ->willReturn([])
        ;

        $this->provider->expects(static::once())
            ->method('getPermissionsBySubject')
            ->with($subject)
            ->willReturn($permissions)
        ;

        $res = $this->pm->getRolePermissions($role, $subject);

        static::assertEquals($expected, $res);
    }

    /**
     * @dataProvider getRoles
     */
    public function testGetRolePermissionsWithConfigPermissions(MockRole $role): void
    {
        $subject = MockOrganizationUser::class;
        $permission = new MockPermission();
        $permission->setOperation('test');
        $permissions = [
            $permission,
        ];
        $expected = [
            new PermissionChecking($permissions[0], true, true),
        ];

        $this->provider->expects(static::once())
            ->method('getPermissions')
            ->with(['ROLE_TEST'])
            ->willReturn([])
        ;

        $this->provider->expects(static::once())
            ->method('getPermissionsBySubject')
            ->with($subject)
            ->willReturn($permissions)
        ;

        $this->pm->addConfig(new PermissionConfig(
            MockOrganizationUser::class,
            ['test'],
            [],
            [
                new PermissionFieldConfig('organization', ['edit']),
            ]
        ));

        $res = $this->pm->getRolePermissions($role, $subject);

        static::assertEquals($expected, $res);
    }

    /**
     * @dataProvider getRoles
     */
    public function testGetRolePermissionsWithClassConfigPermission(MockRole $role): void
    {
        $subject = MockOrganizationUser::class;
        $permission = new MockPermission();
        $permission->setOperation('test');
        $permission->setClass(PermissionProviderInterface::CONFIG_CLASS);
        $permissions = [
            $permission,
        ];
        $expected = [
            new PermissionChecking($permissions[0], true, true),
        ];

        $this->provider->expects(static::once())
            ->method('getPermissionsBySubject')
            ->with($subject)
            ->willReturn([])
        ;

        $this->provider->expects(static::once())
            ->method('getConfigPermissions')
            ->with()
            ->willReturn($permissions)
        ;

        $this->pm->addConfig(new PermissionConfig(
            MockOrganizationUser::class,
            ['test']
        ));

        $res = $this->pm->getRolePermissions($role, $subject);

        static::assertEquals($expected, $res);
    }

    /**
     * @dataProvider getRoles
     */
    public function testGetRolePermissionsWithFieldConfigPermission(MockRole $role): void
    {
        $subject = new FieldVote(MockOrganizationUser::class, 'organization');
        $permission = new MockPermission();
        $permission->setOperation('test');
        $permission->setClass(PermissionProviderInterface::CONFIG_CLASS);
        $permission->setField(PermissionProviderInterface::CONFIG_FIELD);
        $permissions = [
            $permission,
        ];
        $expected = [
            new PermissionChecking($permissions[0], true, true),
        ];

        $this->provider->expects(static::once())
            ->method('getPermissionsBySubject')
            ->with($subject)
            ->willReturn([])
        ;

        $this->provider->expects(static::once())
            ->method('getConfigPermissions')
            ->with()
            ->willReturn($permissions)
        ;

        $this->pm->addConfig(new PermissionConfig(
            MockOrganizationUser::class,
            [],
            [],
            [
                new PermissionFieldConfig('organization', ['test']),
            ]
        ));

        $res = $this->pm->getRolePermissions($role, $subject);

        static::assertEquals($expected, $res);
    }

    /**
     * @dataProvider getRoles
     */
    public function testGetRolePermissionsWithFieldConfigPermissionAndMaster(MockRole $role): void
    {
        $subject = new FieldVote(MockOrganizationUser::class, 'organization');
        $permission = new MockPermission();
        $permission->setOperation('test');
        $permission->setClass(PermissionProviderInterface::CONFIG_CLASS);
        $permission->setField(PermissionProviderInterface::CONFIG_FIELD);
        $permissions = [
            $permission,
        ];
        $expected = [
            new PermissionChecking($permissions[0], false, true),
        ];

        $this->provider->expects(static::once())
            ->method('getPermissionsBySubject')
            ->with($subject)
            ->willReturn([])
        ;

        $this->provider->expects(static::once())
            ->method('getConfigPermissions')
            ->with()
            ->willReturn($permissions)
        ;

        $this->pm->addConfig(new PermissionConfig(MockOrganization::class));
        $this->pm->addConfig(new PermissionConfig(
            MockOrganizationUser::class,
            [],
            [],
            [
                new PermissionFieldConfig('organization', ['test']),
            ],
            'organization'
        ));

        $res = $this->pm->getRolePermissions($role, $subject);

        static::assertEquals($expected, $res);
    }

    /**
     * @dataProvider getRoles
     */
    public function testGetRolePermissionsWithRequiredConfigPermission(MockRole $role): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\PermissionNotFoundException::class);
        $this->expectExceptionMessage('The permission "test" for "Klipper\\Component\\Security\\Tests\\Fixtures\\Model\\MockOrganizationUser" is not found ant it required by the permission configuration');

        $subject = MockOrganizationUser::class;
        $permissions = [];

        $this->provider->expects(static::once())
            ->method('getPermissionsBySubject')
            ->with($subject)
            ->willReturn($permissions)
        ;

        $this->provider->expects(static::once())
            ->method('getConfigPermissions')
            ->with()
            ->willReturn([])
        ;

        $this->pm->addConfig(new PermissionConfig(
            MockOrganizationUser::class,
            ['test']
        ));

        $this->pm->getRolePermissions($role, $subject);
    }

    public function testGetFieldRolePermissions(): void
    {
        $role = new MockRole('ROLE_TEST');
        $subject = MockObject::class;
        $field = 'name';
        $permission = new MockPermission();
        $permission->setOperation('test');
        $permission->setClass($subject);
        $permission->setField($field);
        $permissions = [
            $permission,
        ];
        $expected = [
            new PermissionChecking($permissions[0], true),
        ];

        $this->provider->expects(static::once())
            ->method('getPermissionsBySubject')
            ->with(new FieldVote($subject, $field))
            ->willReturn($permissions)
        ;

        $res = $this->pm->getRoleFieldPermissions($role, $subject, $field);

        static::assertEquals($expected, $res);
    }

    public function testPreloadPermissions(): void
    {
        $objects = [new MockObject('foo')];

        $pm = $this->pm->preloadPermissions($objects);

        static::assertSame($this->pm, $pm);
    }

    public function testPreloadPermissionsWithSharing(): void
    {
        $objects = [new MockObject('foo')];

        /** @var MockObject|SharingManagerInterface $sharingManager */
        $sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $sharingManager->expects(static::once())
            ->method('preloadPermissions')
            ->with($objects)
        ;

        $this->pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor,
            null,
            $sharingManager
        );

        $pm = $this->pm->preloadPermissions($objects);

        static::assertSame($this->pm, $pm);
    }

    public function testResetPreloadPermissions(): void
    {
        $objects = [
            new MockObject('foo'),
        ];

        $pm = $this->pm->resetPreloadPermissions($objects);

        static::assertSame($this->pm, $pm);
    }

    public function testResetPreloadPermissionsWithSharing(): void
    {
        $objects = [new MockObject('foo')];

        /** @var MockObject|SharingManagerInterface $sharingManager */
        $sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $sharingManager->expects(static::once())
            ->method('resetPreloadPermissions')
            ->with($objects)
        ;

        $this->pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor,
            null,
            $sharingManager
        );

        $pm = $this->pm->resetPreloadPermissions($objects);

        static::assertSame($this->pm, $pm);
    }

    public function testEvents(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = MockObject::class;
        $permission = 'view';
        $perm = new MockPermission();
        $perm->setOperation($permission);
        $perm->setClass(MockObject::class);
        $preLoad = false;
        $postLoad = false;
        $checkPerm = false;

        $this->dispatcher->addListener(PreLoadPermissionsEvent::class, function (PreLoadPermissionsEvent $event) use ($sids, &$preLoad): void {
            $preLoad = true;
            $this->assertSame($sids, $event->getSecurityIdentities());
        });

        $this->dispatcher->addListener(PostLoadPermissionsEvent::class, function (PostLoadPermissionsEvent $event) use ($sids, &$postLoad): void {
            $postLoad = true;
            $this->assertSame($sids, $event->getSecurityIdentities());
        });

        $this->dispatcher->addListener(CheckPermissionEvent::class, function (CheckPermissionEvent $event) use ($sids, &$checkPerm): void {
            $checkPerm = true;
            $this->assertSame($sids, $event->getSecurityIdentities());
        });

        $this->pm->addConfig(new PermissionConfig(MockObject::class));

        $this->provider->expects(static::once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([$perm])
        ;

        static::assertTrue($this->pm->isGranted($sids, $permission, $object));
        static::assertTrue($preLoad);
        static::assertTrue($postLoad);
        static::assertTrue($checkPerm);
    }

    public function testOverrideGrantValueWithEvent(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = MockObject::class;
        $permission = 'view';
        $checkPerm = false;

        $this->dispatcher->addListener(CheckPermissionEvent::class, static function (CheckPermissionEvent $event) use (&$checkPerm): void {
            $checkPerm = true;
            $event->setGranted(true);
        });

        $this->pm->addConfig(new PermissionConfig(MockObject::class));

        $this->provider->expects(static::once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([])
        ;

        static::assertTrue($this->pm->isGranted($sids, $permission, $object));
        static::assertTrue($checkPerm);
    }
}
