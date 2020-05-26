<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Sharing;

use Klipper\Component\Security\Event\SharingDisabledEvent;
use Klipper\Component\Security\Event\SharingEnabledEvent;
use Klipper\Component\Security\Identity\SubjectIdentity;
use Klipper\Component\Security\Identity\SubjectIdentityInterface;
use Klipper\Component\Security\Sharing\Loader\IdentityConfigurationLoader;
use Klipper\Component\Security\Sharing\Loader\SubjectConfigurationLoader;
use Klipper\Component\Security\Sharing\SharingFactory;
use Klipper\Component\Security\Sharing\SharingIdentityConfig;
use Klipper\Component\Security\Sharing\SharingManager;
use Klipper\Component\Security\Sharing\SharingProviderInterface;
use Klipper\Component\Security\Sharing\SharingSubjectConfig;
use Klipper\Component\Security\SharingVisibilities;
use Klipper\Component\Security\Tests\Fixtures\Model\MockGroup;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObject;
use Klipper\Component\Security\Tests\Fixtures\Model\MockPermission;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRole;
use Klipper\Component\Security\Tests\Fixtures\Model\MockSharing;
use Klipper\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SharingManagerTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|SharingProviderInterface
     */
    protected $provider;

    /**
     * @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $dispatcher;

    /**
     * @var SharingManager
     */
    protected $sm;

    protected function setUp(): void
    {
        $this->provider = $this->getMockBuilder(SharingProviderInterface::class)->getMock();
        $this->dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();

        $this->provider->expects(static::atLeastOnce())
            ->method('setSharingManager')
        ;

        $this->sm = new SharingManager($this->provider);
        $this->sm->setEventDispatcher($this->dispatcher);
    }

    public function testIsEnabled(): void
    {
        $this->dispatcher->expects(static::at(0))
            ->method('dispatch')
            ->with(new SharingDisabledEvent())
        ;

        $this->dispatcher->expects(static::at(1))
            ->method('dispatch')
            ->with(new SharingEnabledEvent())
        ;

        static::assertTrue($this->sm->isEnabled());

        $this->sm->setEnabled(false);
        static::assertFalse($this->sm->isEnabled());

        $this->sm->setEnabled(true);
        static::assertTrue($this->sm->isEnabled());
    }

    public function testHasSubjectConfig(): void
    {
        $pm = new SharingManager($this->provider, new SharingFactory(
            new SubjectConfigurationLoader([
                new SharingSubjectConfig(MockObject::class),
            ]),
            new IdentityConfigurationLoader([]),
            'resource'
        ));

        static::assertTrue($pm->hasSubjectConfig(MockObject::class));
    }

    public function testHasIdentityConfig(): void
    {
        $pm = new SharingManager($this->provider, new SharingFactory(
            new SubjectConfigurationLoader([]),
            new IdentityConfigurationLoader([
                new SharingIdentityConfig(MockRole::class),
            ]),
            'resource'
        ));

        static::assertTrue($pm->hasIdentityConfig(MockRole::class));
    }

    public function testHasNotSubjectConfig(): void
    {
        static::assertFalse($this->sm->hasSubjectConfig(MockObject::class));
    }

    public function testHasNotIdentityConfig(): void
    {
        static::assertFalse($this->sm->hasIdentityConfig(MockRole::class));
    }

    public function testAddSubjectConfig(): void
    {
        static::assertFalse($this->sm->hasSubjectConfig(MockObject::class));

        $this->sm->addSubjectConfig(new SharingSubjectConfig(MockObject::class));

        static::assertTrue($this->sm->hasSubjectConfig(MockObject::class));
    }

    public function testAddIdentityConfig(): void
    {
        static::assertFalse($this->sm->hasIdentityConfig(MockRole::class));

        $this->sm->addIdentityConfig(new SharingIdentityConfig(MockRole::class));

        static::assertTrue($this->sm->hasIdentityConfig(MockRole::class));
    }

    public function testAddIdentityConfigWithAlreadyExistingAlias(): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\AlreadyConfigurationAliasExistingException::class);
        $this->expectExceptionMessage('The alias "foo" of sharing identity configuration for the class "Klipper\\Component\\Security\\Tests\\Fixtures\\Model\\MockGroup');

        $this->sm->addIdentityConfig(new SharingIdentityConfig(MockRole::class, 'foo'));
        $this->sm->addIdentityConfig(new SharingIdentityConfig(MockGroup::class, 'foo'));
    }

    public function testGetSubjectConfig(): void
    {
        $config = new SharingSubjectConfig(MockObject::class);
        $this->sm->addSubjectConfig($config);

        static::assertTrue($this->sm->hasSubjectConfig(MockObject::class));
        static::assertSame($config, $this->sm->getSubjectConfig(MockObject::class));
    }

    public function testGetIdentityConfig(): void
    {
        $config = new SharingIdentityConfig(MockRole::class, 'role');
        $this->sm->addIdentityConfig($config);

        static::assertTrue($this->sm->hasIdentityConfig(MockRole::class));
        static::assertSame($config, $this->sm->getIdentityConfig(MockRole::class));
        static::assertTrue($this->sm->hasIdentityConfig('role'));
        static::assertSame($config, $this->sm->getIdentityConfig('role'));
    }

    public function testGetSubjectConfigWithNotManagedClass(): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\SharingSubjectConfigNotFoundException::class);
        $this->expectExceptionMessage('The sharing subject configuration for the class "Klipper\\Component\\Security\\Tests\\Fixtures\\Model\\MockRole" is not found');

        $this->sm->getSubjectConfig(MockRole::class);
    }

    public function testGetIdentityConfigWithNotManagedClass(): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\SharingIdentityConfigNotFoundException::class);
        $this->expectExceptionMessage('The sharing identity configuration for the class "Klipper\\Component\\Security\\Tests\\Fixtures\\Model\\MockRole" is not found');

        $this->sm->getIdentityConfig(MockRole::class);
    }

    public function testGetSubjectConfigs(): void
    {
        $config = new SharingSubjectConfig(MockRole::class);
        $this->sm->addSubjectConfig($config);

        static::assertSame([$config], $this->sm->getSubjectConfigs());
    }

    public function testGetIdentityConfigs(): void
    {
        $config = new SharingIdentityConfig(MockRole::class);
        $this->sm->addIdentityConfig($config);

        static::assertSame([$config], $this->sm->getIdentityConfigs());
    }

    public function testHasIdentityRoleable(): void
    {
        static::assertFalse($this->sm->hasIdentityRoleable());

        $config = new SharingIdentityConfig(MockRole::class, null, true);
        $this->sm->addIdentityConfig($config);

        static::assertTrue($this->sm->hasIdentityRoleable());
    }

    public function testHasIdentityPermissible(): void
    {
        static::assertFalse($this->sm->hasIdentityPermissible());

        $config = new SharingIdentityConfig(MockRole::class, null, false, true);
        $this->sm->addIdentityConfig($config);

        static::assertTrue($this->sm->hasIdentityPermissible());
    }

    public function testHasSharingVisibilityWithoutConfig(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|SubjectIdentityInterface $subject */
        $subject = $this->getMockBuilder(SubjectIdentityInterface::class)->getMock();
        $subject->expects(static::once())
            ->method('getType')
            ->willReturn(MockObject::class)
        ;

        static::assertFalse($this->sm->hasSharingVisibility($subject));
    }

    public function getSharingVisibilities(): array
    {
        return [
            [SharingVisibilities::TYPE_NONE, false],
            [SharingVisibilities::TYPE_PUBLIC, true],
            [SharingVisibilities::TYPE_PRIVATE, true],
        ];
    }

    /**
     * @dataProvider getSharingVisibilities
     *
     * @param string $visibility The sharing visibility
     * @param bool   $result     The result
     */
    public function testHasSharingVisibility($visibility, $result): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|SubjectIdentityInterface $subject */
        $subject = $this->getMockBuilder(SubjectIdentityInterface::class)->getMock();
        $subject->expects(static::once())
            ->method('getType')
            ->willReturn(MockObject::class)
        ;

        $this->sm->addSubjectConfig(new SharingSubjectConfig(MockObject::class, $visibility));
        $this->sm->addSubjectConfig(new SharingSubjectConfig(MockObject::class, $visibility));

        static::assertSame($result, $this->sm->hasSharingVisibility($subject));
    }

    public function testResetPreloadPermissions(): void
    {
        $object = new MockObject('foo', 42);
        $sm = $this->sm->resetPreloadPermissions([$object]);

        static::assertSame($sm, $this->sm);
    }

    public function testResetPreloadPermissionsWithInvalidSubjectIdentity(): void
    {
        $sm = $this->sm->resetPreloadPermissions([42]);

        static::assertSame($sm, $this->sm);
    }

    public function testClear(): void
    {
        $sm = $this->sm->clear();

        static::assertSame($sm, $this->sm);
    }

    public function testIsGranted(): void
    {
        $operation = 'view';
        $field = null;
        $object = new MockObject('foo', 42);
        $subject = SubjectIdentity::fromObject($object);

        $newObject = new MockObject('foo', null);

        $perm = new MockPermission();
        $perm->setOperation('view');
        $sharing = new MockSharing();
        $sharing->setSubjectClass(MockObject::class);
        $sharing->setSubjectId(42);
        $sharing->setIdentityClass(MockRole::class);
        $sharing->setIdentityName('ROLE_USER');
        $sharing->getPermissions()->add($perm);

        $sharing2 = new MockSharing();
        $sharing2->setSubjectClass(MockObject::class);
        $sharing2->setSubjectId(42);
        $sharing2->setIdentityClass(MockUserRoleable::class);
        $sharing2->setIdentityName('user.test');
        $sharing2->setRoles(['ROLE_TEST']);

        $this->provider->expects(static::once())
            ->method('getSharingEntries')
            ->with([SubjectIdentity::fromObject($object)])
            ->willReturn([$sharing, $sharing2])
        ;

        $roleTest = new MockRole('ROLE_TEST');
        $perm2 = new MockPermission();
        $perm2->setOperation('test');
        $roleTest->addPermission($perm2);

        $this->provider->expects(static::once())
            ->method('getPermissionRoles')
            ->with(['ROLE_TEST'])
            ->willReturn([$roleTest])
        ;

        $sConfig = new SharingSubjectConfig(MockObject::class, SharingVisibilities::TYPE_PRIVATE);
        $this->sm->addSubjectConfig($sConfig);

        $iConfig = new SharingIdentityConfig(MockRole::class, 'role', false, true);
        $this->sm->addIdentityConfig($iConfig);
        $iConfig2 = new SharingIdentityConfig(MockUserRoleable::class, 'user', true);
        $this->sm->addIdentityConfig($iConfig2);

        $this->sm->preloadPermissions([$object, $newObject]);
        $this->sm->preloadRolePermissions([$subject]);

        static::assertTrue($this->sm->isGranted($operation, $subject, $field));
    }

    public function testIsGrantedWithField(): void
    {
        $operation = 'view';
        $field = 'name';
        $object = new MockObject('foo', 42);
        $subject = SubjectIdentity::fromObject($object);

        static::assertFalse($this->sm->isGranted($operation, $subject, $field));
    }

    public function testIsGrantedWithoutIdentityConfigRoleable(): void
    {
        $operation = 'view';
        $field = null;
        $object = new MockObject('foo', 42);
        $subject = SubjectIdentity::fromObject($object);

        $perm = new MockPermission();
        $perm->setOperation('view');
        $sharing = new MockSharing();
        $sharing->setSubjectClass(MockObject::class);
        $sharing->setSubjectId(42);
        $sharing->setIdentityClass(MockRole::class);
        $sharing->setIdentityName('ROLE_USER');
        $sharing->getPermissions()->add($perm);

        $this->provider->expects(static::once())
            ->method('getSharingEntries')
            ->with([SubjectIdentity::fromObject($object)])
            ->willReturn([$sharing])
        ;

        $sConfig = new SharingSubjectConfig(MockObject::class, SharingVisibilities::TYPE_PRIVATE);
        $this->sm->addSubjectConfig($sConfig);

        $iConfig = new SharingIdentityConfig(MockRole::class, 'role', false, true);
        $this->sm->addIdentityConfig($iConfig);

        $this->sm->preloadPermissions([$object]);

        static::assertTrue($this->sm->isGranted($operation, $subject, $field));
    }

    public function testRenameIdentity(): void
    {
        $this->provider->expects(static::once())
            ->method('renameIdentity')
            ->with(MockRole::class, 'ROLE_FOO', 'ROLE_BAR')
            ->willReturn('QUERY')
        ;

        $this->sm->renameIdentity(MockRole::class, 'ROLE_FOO', 'ROLE_BAR');
    }

    public function testDeletes(): void
    {
        $ids = [42, 50];

        $this->provider->expects(static::once())
            ->method('deletes')
            ->with($ids)
            ->willReturn('QUERY')
        ;

        $this->sm->deletes($ids);
    }

    public function testDeleteIdentity(): void
    {
        $this->provider->expects(static::once())
            ->method('deleteIdentity')
            ->with(MockRole::class, 'ROLE_FOO')
            ->willReturn('QUERY')
        ;

        $this->sm->deleteIdentity(MockRole::class, 'ROLE_FOO');
    }
}
