<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Permission;

use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Klipper\Component\Security\Event\CheckPermissionEvent;
use Klipper\Component\Security\Event\PostLoadPermissionsEvent;
use Klipper\Component\Security\Event\PreLoadPermissionsEvent;
use Klipper\Component\Security\Exception\PermissionNotFoundException;
use Klipper\Component\Security\Identity\IdentityUtils;
use Klipper\Component\Security\Identity\RoleSecurityIdentity;
use Klipper\Component\Security\Identity\SecurityIdentityInterface;
use Klipper\Component\Security\Identity\SubjectIdentity;
use Klipper\Component\Security\Identity\SubjectIdentityInterface;
use Klipper\Component\Security\Identity\SubjectUtils;
use Klipper\Component\Security\Model\PermissionChecking;
use Klipper\Component\Security\Model\RoleInterface;
use Klipper\Component\Security\Sharing\SharingManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Permission manager.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PermissionManager extends AbstractPermissionManager
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var PermissionProviderInterface
     */
    protected $provider;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var null|array
     */
    protected $cacheConfigPermissions;

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface        $dispatcher       The event dispatcher
     * @param PermissionProviderInterface     $provider         The permission provider
     * @param PropertyAccessorInterface       $propertyAccessor The property accessor
     * @param null|PermissionFactoryInterface $factory          The permission factory
     * @param null|SharingManagerInterface    $sharingManager   The sharing manager
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        PermissionProviderInterface $provider,
        PropertyAccessorInterface $propertyAccessor,
        ?PermissionFactoryInterface $factory = null,
        ?SharingManagerInterface $sharingManager = null
    ) {
        parent::__construct($factory, $sharingManager);

        $this->dispatcher = $dispatcher;
        $this->provider = $provider;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMaster($subject): ?SubjectIdentityInterface
    {
        if (null !== $subject) {
            $subject = SubjectUtils::getSubjectIdentity($subject);

            if ($this->hasConfig($subject->getType())) {
                $config = $this->getConfig($subject->getType());

                if (null !== $config->getMaster()) {
                    if (\is_object($subject->getObject())) {
                        $value = $this->propertyAccessor->getValue($subject->getObject(), $config->getMaster());

                        if (\is_object($value)) {
                            $subject = SubjectIdentity::fromObject($value);
                        }
                    } else {
                        $subject = SubjectIdentity::fromClassname($this->provider->getMasterClass($config));
                    }
                }
            }
        }

        return $subject;
    }

    /**
     * {@inheritdoc}
     */
    protected function doIsManaged(SubjectIdentityInterface $subject, ?string $field = null): bool
    {
        if ($this->hasConfig($subject->getType())) {
            if (null === $field) {
                return true;
            }

            return $this->getConfig($subject->getType())->hasField($field);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doIsGranted(array $sids, array $permissions, ?SubjectIdentityInterface $subject = null, ?string $field = null): bool
    {
        if (null !== $subject) {
            $this->preloadPermissions([$subject]);
            $this->preloadSharingRolePermissions([$subject]);
        }

        $id = $this->loadPermissions($sids);

        foreach ($permissions as $operation) {
            if (!$this->doIsGrantedPermission($id, $sids, $operation, $subject, $field)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetRolePermissions(RoleInterface $role, $subject = null): array
    {
        $permissions = [];
        $sid = new RoleSecurityIdentity(ClassUtils::getClass($role), $role->getName());
        $contexts = $this->buildContexts($role);
        list($class, $field) = PermissionUtils::getClassAndField($subject, true);

        foreach ($this->provider->getPermissionsBySubject($subject, $contexts) as $permission) {
            $operation = $permission->getOperation();
            $granted = $this->isGranted([$sid], [$operation], $subject);
            $isConfig = $this->isConfigPermission($operation, $class, $field);
            $permissions[$operation] = new PermissionChecking($permission, $granted, $isConfig);
        }

        return $this->validateRolePermissions($sid, $permissions, $subject, $class, $field);
    }

    /**
     * Validate the role permissions.
     *
     * @param RoleSecurityIdentity                                  $sid         The role security identity
     * @param PermissionChecking[]                                  $permissions The permission checking
     * @param null|FieldVote|object|string|SubjectIdentityInterface $subject     The object or class name or field vote
     * @param null|string                                           $class       The class name
     * @param null|string                                           $field       The field name
     *
     * @return PermissionChecking[]
     */
    private function validateRolePermissions(RoleSecurityIdentity $sid, array $permissions, $subject = null, ?string $class = null, ?string $field = null): array
    {
        $configOperations = $this->getConfigPermissionOperations($class, $field);

        foreach ($configOperations as $configOperation) {
            if (!isset($permissions[$configOperation])) {
                if (null !== $sp = $this->getConfigPermission($sid, $configOperation, $subject, $class, $field)) {
                    $permissions[$sp->getPermission()->getOperation()] = $sp;

                    continue;
                }

                throw new PermissionNotFoundException($configOperation, $class, $field);
            }
        }

        return array_values($permissions);
    }

    /**
     * Get the config permission.
     *
     * @param RoleSecurityIdentity                                  $sid       The role security identity
     * @param string                                                $operation The operation
     * @param null|FieldVote|object|string|SubjectIdentityInterface $subject   The object or class name or field vote
     * @param null|string                                           $class     The class name
     * @param null|string                                           $field     The field name
     */
    private function getConfigPermission(RoleSecurityIdentity $sid, string $operation, $subject = null, ?string $class = null, ?string $field = null): ?PermissionChecking
    {
        $sps = $this->getConfigPermissions();
        $field = null !== $field ? PermissionProviderInterface::CONFIG_FIELD : null;
        $fieldAction = PermissionUtils::getMapAction($field);
        $pc = null;

        if (isset($sps[PermissionProviderInterface::CONFIG_CLASS][$fieldAction][$operation])) {
            $sp = $sps[PermissionProviderInterface::CONFIG_CLASS][$fieldAction][$operation];
            $pc = new PermissionChecking($sp, $this->isConfigGranted($sid, $operation, $subject, $class), true);
        }

        return $pc;
    }

    /**
     * Check if the config permission is granted.
     *
     * @param RoleSecurityIdentity                                  $sid       The role security identity
     * @param string                                                $operation The operation
     * @param null|FieldVote|object|string|SubjectIdentityInterface $subject   The object or class name or field vote
     * @param null|string                                           $class     The class name
     */
    private function isConfigGranted(RoleSecurityIdentity $sid, string $operation, $subject = null, ?string $class = null): bool
    {
        $granted = true;

        if (null !== $class && $this->hasConfig($class)) {
            $config = $this->getConfig($class);

            if (null !== $config->getMaster()) {
                $realOperation = $config->getMappingPermission($operation);
                $granted = $this->isGranted([$sid], [$realOperation], $subject);
            }
        }

        return $granted;
    }

    /**
     * Get the config permissions.
     */
    private function getConfigPermissions(): array
    {
        if (null === $this->cacheConfigPermissions) {
            $sps = $this->provider->getConfigPermissions();
            $this->cacheConfigPermissions = [];

            foreach ($sps as $sp) {
                $classAction = PermissionUtils::getMapAction($sp->getClass());
                $fieldAction = PermissionUtils::getMapAction($sp->getField());
                $this->cacheConfigPermissions[$classAction][$fieldAction][$sp->getOperation()] = $sp;
            }
        }

        return $this->cacheConfigPermissions;
    }

    /**
     * Action to determine whether access is granted for a specific operation.
     *
     * @param string                        $id        The cache id
     * @param SecurityIdentityInterface[]   $sids      The security identities
     * @param string                        $operation The operation
     * @param null|SubjectIdentityInterface $subject   The subject
     * @param null|string                   $field     The field of subject
     */
    private function doIsGrantedPermission($id, array $sids, string $operation, ?SubjectIdentityInterface $subject = null, ?string $field = null): bool
    {
        $event = new CheckPermissionEvent($sids, $this->cache[$id], $operation, $subject, $field);
        $this->dispatcher->dispatch($event);

        if (\is_bool($event->isGranted())) {
            return $event->isGranted();
        }

        $classAction = PermissionUtils::getMapAction(null !== $subject ? $subject->getType() : null);
        $fieldAction = PermissionUtils::getMapAction($field);

        return isset($this->cache[$id][$classAction][$fieldAction][$operation])
            || $this->isSharingGranted($operation, $subject, $field);
    }

    /**
     * Load the permissions of sharing roles.
     *
     * @param SubjectIdentityInterface[] $subjects The subjects
     */
    private function preloadSharingRolePermissions(array $subjects): void
    {
        if (null !== $this->sharingManager) {
            $this->sharingManager->preloadRolePermissions($subjects);
        }
    }

    /**
     * Load the permissions of roles and returns the id of cache.
     *
     * @param SecurityIdentityInterface[] $sids The security identities
     *
     * @return string The cache id
     */
    private function loadPermissions(array $sids): string
    {
        $roles = IdentityUtils::filterRolesIdentities($sids);
        $id = implode('|', $roles);

        if (!\array_key_exists($id, $this->cache)) {
            $this->cache[$id] = [];
            $preEvent = new PreLoadPermissionsEvent($sids, $roles);
            $this->dispatcher->dispatch($preEvent);
            $perms = $this->provider->getPermissions($roles);

            $this->buildSystemPermissions($id);

            foreach ($perms as $perm) {
                $class = PermissionUtils::getMapAction($perm->getClass());
                $field = PermissionUtils::getMapAction($perm->getField());
                $this->cache[$id][$class][$field][$perm->getOperation()] = true;
            }

            $postEvent = new PostLoadPermissionsEvent($sids, $roles, $this->cache[$id]);
            $this->dispatcher->dispatch($postEvent);
            $this->cache[$id] = $postEvent->getPermissionMap();
        }

        return $id;
    }

    /**
     * Check if the permission operation is defined by the config.
     *
     * @param string      $operation The permission operation
     * @param null|string $class     The class name
     * @param null|string $field     The field
     */
    private function isConfigPermission(string $operation, ?string $class = null, ?string $field = null): bool
    {
        $map = $this->getMapConfigPermissions();
        $class = PermissionUtils::getMapAction($class);
        $field = PermissionUtils::getMapAction($field);

        return isset($map[$class][$field][$operation]);
    }

    /**
     * Get the config operations of the subject.
     *
     * @param null|string $class The class name
     * @param null|string $field The field
     *
     * @return string[]
     */
    private function getConfigPermissionOperations(?string $class = null, ?string $field = null): array
    {
        $map = $this->getMapConfigPermissions();
        $class = PermissionUtils::getMapAction($class);
        $field = PermissionUtils::getMapAction($field);
        $operations = [];

        if (isset($map[$class][$field])) {
            $operations = array_keys($map[$class][$field]);
        }

        return $operations;
    }

    /**
     * Get the map of the config permissions.
     */
    private function getMapConfigPermissions(): array
    {
        $id = '_config';

        if (!\array_key_exists($id, $this->cache)) {
            $this->cache[$id] = [];
            $this->buildSystemPermissions($id);
        }

        return $this->cache[$id];
    }

    /**
     * Build the system permissions.
     *
     * @param string $id The cache id
     */
    private function buildSystemPermissions(string $id): void
    {
        foreach ($this->configs as $config) {
            foreach ($config->getOperations() as $operation) {
                $field = PermissionUtils::getMapAction(null);
                $this->cache[$id][$config->getType()][$field][$operation] = true;
            }

            foreach ($config->getFields() as $fieldConfig) {
                foreach ($fieldConfig->getOperations() as $operation) {
                    $this->cache[$id][$config->getType()][$fieldConfig->getField()][$operation] = true;
                }
            }
        }
    }

    /**
     * Check if the access is granted by a sharing entry.
     *
     * @param string                        $operation The operation
     * @param null|SubjectIdentityInterface $subject   The subject
     * @param null|string                   $field     The field of subject
     */
    private function isSharingGranted(string $operation, ?SubjectIdentityInterface $subject = null, ?string $field = null): bool
    {
        return null !== $this->sharingManager
            ? $this->sharingManager->isGranted($operation, $subject, $field)
            : false;
    }
}
