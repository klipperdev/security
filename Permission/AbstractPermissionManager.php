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
use Klipper\Component\Security\Exception\InvalidSubjectIdentityException;
use Klipper\Component\Security\Exception\PermissionConfigNotFoundException;
use Klipper\Component\Security\Identity\SecurityIdentityInterface;
use Klipper\Component\Security\Identity\SubjectIdentityInterface;
use Klipper\Component\Security\Model\PermissionChecking;
use Klipper\Component\Security\Model\RoleInterface;
use Klipper\Component\Security\Model\Traits\OrganizationalInterface;
use Klipper\Component\Security\PermissionContexts;
use Klipper\Component\Security\Sharing\SharingManagerInterface;

/**
 * Abstract permission manager.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractPermissionManager implements PermissionManagerInterface
{
    protected ?PermissionFactoryInterface $factory;

    protected ?SharingManagerInterface $sharingManager;

    /**
     * @var PermissionConfigInterface[]
     */
    protected array $configs = [];

    protected bool $enabled = true;

    protected array $cache = [];

    protected bool $initialized = false;

    /**
     * @param null|PermissionFactoryInterface $factory        The permission factory
     * @param null|SharingManagerInterface    $sharingManager The sharing manager
     */
    public function __construct(
        ?PermissionFactoryInterface $factory = null,
        ?SharingManagerInterface $sharingManager = null
    ) {
        $this->factory = $factory;
        $this->sharingManager = $sharingManager;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        if (null !== $this->sharingManager) {
            $this->sharingManager->setEnabled($enabled);
        }

        return $this;
    }

    public function addConfig(PermissionConfigInterface $config): void
    {
        $this->configs[$config->getType()] = $config;
    }

    public function hasConfig(string $class): bool
    {
        $this->init();

        return isset($this->configs[ClassUtils::getRealClass($class)]);
    }

    public function getConfig(string $class): PermissionConfigInterface
    {
        $class = ClassUtils::getRealClass($class);

        if (!$this->hasConfig($class)) {
            throw new PermissionConfigNotFoundException($class);
        }

        return $this->configs[$class];
    }

    public function getConfigs(): array
    {
        $this->init();

        return $this->configs;
    }

    public function isManaged($subject): bool
    {
        try {
            $this->init();
            /** @var SubjectIdentityInterface $subject */
            list($subject, $field) = PermissionUtils::getSubjectAndField($subject);

            return $this->doIsManaged($subject, $field);
        } catch (InvalidSubjectIdentityException $e) {
            // do nothing
        }

        return false;
    }

    public function isFieldManaged($subject, string $field): bool
    {
        return $this->isManaged(new FieldVote($subject, $field));
    }

    public function isGranted(array $sids, $permissions, $subject = null): bool
    {
        try {
            $this->init();
            /** @var null|SubjectIdentityInterface $subject */
            list($subject, $field) = PermissionUtils::getSubjectAndField($subject, true);
            list($permissions, $subject, $field) = $this->getMasterPermissions(
                (array) $permissions,
                $subject,
                $field
            );

            if (null !== $subject && !$this->doIsManaged($subject, $field)) {
                return true;
            }

            return $this->doIsGranted($sids, $this->getRealPermissions($permissions, $subject, $field), $subject, $field);
        } catch (InvalidSubjectIdentityException $e) {
            // do nothing
        }

        return false;
    }

    public function isFieldGranted(array $sids, $permissions, $subject, string $field): bool
    {
        return $this->isGranted($sids, $permissions, new FieldVote($subject, $field));
    }

    public function getRolePermissions(RoleInterface $role, $subject = null): array
    {
        $this->init();

        return $this->doGetRolePermissions($role, $subject);
    }

    public function getRoleFieldPermissions(RoleInterface $role, $subject, string $field): array
    {
        $this->init();

        return $this->getRolePermissions($role, new FieldVote($subject, $field));
    }

    public function preloadPermissions(array $objects): self
    {
        $this->init();

        if (null !== $this->sharingManager) {
            $this->sharingManager->preloadPermissions($objects);
        }

        return $this;
    }

    public function resetPreloadPermissions(array $objects): self
    {
        if (null !== $this->sharingManager) {
            $this->sharingManager->resetPreloadPermissions($objects);
        }

        return $this;
    }

    public function clear(): self
    {
        $this->cache = [];

        if (null !== $this->sharingManager) {
            $this->sharingManager->clear();
        }

        return $this;
    }

    /**
     * Build the permission contexts for the role.
     *
     * @param RoleInterface $role The role
     *
     * @return null|string[]
     */
    protected function buildContexts(RoleInterface $role): ?array
    {
        $contexts = null;

        if ($role instanceof OrganizationalInterface) {
            $contexts = [null !== $role->getOrganization() ? PermissionContexts::ORGANIZATION_ROLE : PermissionContexts::ROLE];
        }

        return $contexts;
    }

    /**
     * Initialize the configurations.
     */
    protected function init(): void
    {
        if (!$this->initialized) {
            $this->initialized = true;

            if (null !== $this->factory) {
                foreach ($this->factory->createConfigurations() as $config) {
                    $this->addConfig($config);
                }
            }
        }
    }

    /**
     * Get the master subject.
     *
     * @param null|object|string|SubjectIdentityInterface $subject The subject instance or classname
     */
    abstract protected function getMaster($subject): ?SubjectIdentityInterface;

    /**
     * Action to check if the subject is managed.
     *
     * @param SubjectIdentityInterface $subject The subject identity
     * @param null|string              $field   The field name
     */
    abstract protected function doIsManaged(SubjectIdentityInterface $subject, ?string $field = null): bool;

    /**
     * Action to determine whether access is granted.
     *
     * @param SecurityIdentityInterface[]   $sids        The security identities
     * @param string[]                      $permissions The required permissions
     * @param null|SubjectIdentityInterface $subject     The subject
     * @param null|string                   $field       The field of subject
     */
    abstract protected function doIsGranted(array $sids, array $permissions, ?SubjectIdentityInterface $subject = null, ?string $field = null): bool;

    /**
     * Action to retrieve the permissions of role and subject.
     *
     * @param RoleInterface                                         $role    The role
     * @param null|FieldVote|object|string|SubjectIdentityInterface $subject The object or class name or field vote
     *
     * @return PermissionChecking[]
     */
    abstract protected function doGetRolePermissions(RoleInterface $role, $subject = null): array;

    /**
     * Get the real permissions.
     *
     * @param string[]                      $permissions The permissions
     * @param null|SubjectIdentityInterface $subject     The subject identity
     * @param null|string                   $field       The field name
     *
     * @return string[]
     */
    private function getRealPermissions(array $permissions, ?SubjectIdentityInterface $subject = null, ?string $field = null): array
    {
        if (null !== $subject && $this->hasConfig($subject->getType())) {
            $config = $this->getConfig($subject->getType());

            if (null !== $field && $config->hasField($field)) {
                $config = $config->getField($field);
            }

            foreach ($permissions as $key => &$permission) {
                $permission = $config->getMappingPermission($permission);
            }
        }

        return $permissions;
    }

    /**
     * Get the master subject and permissions.
     *
     * @param string[]                      $permissions The permissions
     * @param null|SubjectIdentityInterface $subject     The subject identity
     * @param null|string                   $field       The field name
     */
    private function getMasterPermissions(array $permissions, ?SubjectIdentityInterface $subject, ?string $field): array
    {
        $master = $this->getMaster($subject);

        if (null !== $subject && null !== $master && $subject !== $master) {
            if (null !== $field) {
                $permissions = $this->buildMasterFieldPermissions($subject, $permissions);
            }

            $subject = $master;
            $field = null;
        }

        return [$permissions, $subject, $field];
    }

    /**
     * Build the master permissions.
     *
     * @param SubjectIdentityInterface $subject     The subject identity
     * @param string[]                 $permissions The permissions
     *
     * @return string[]
     */
    private function buildMasterFieldPermissions(SubjectIdentityInterface $subject, array $permissions): array
    {
        if ($this->hasConfig($subject->getType())) {
            $map = $this->getConfig($subject->getType())->getMasterFieldMappingPermissions();

            foreach ($permissions as &$permission) {
                if (false !== $key = array_search($permission, $map, true)) {
                    $permission = $key;
                }
            }
        }

        return $permissions;
    }
}
