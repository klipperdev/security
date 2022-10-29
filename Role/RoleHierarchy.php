<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Role;

use Doctrine\Persistence\ManagerRegistry as ManagerRegistryInterface;
use Klipper\Component\DoctrineExtensions\Util\SqlFilterUtil;
use Klipper\Component\DoctrineExtra\Util\ManagerUtils;
use Klipper\Component\Security\Event\PostReachableRoleEvent;
use Klipper\Component\Security\Event\PreReachableRoleEvent;
use Klipper\Component\Security\Model\RoleHierarchicalInterface;
use Klipper\Component\Security\Model\RoleInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchy as BaseRoleHierarchy;

/**
 * RoleHierarchy defines a role hierarchy.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RoleHierarchy extends BaseRoleHierarchy
{
    private ManagerRegistryInterface $registry;

    private string $roleClassname;

    private array $cacheExec = [];

    private ?CacheItemPoolInterface $cache;

    private ?EventDispatcherInterface $eventDispatcher = null;

    /**
     * @param array                       $hierarchy     An array defining the hierarchy
     * @param ManagerRegistryInterface    $registry      The doctrine registry
     * @param null|CacheItemPoolInterface $cache         The cache
     * @param string                      $roleClassname The classname of role
     */
    public function __construct(
        array $hierarchy,
        ManagerRegistryInterface $registry,
        CacheItemPoolInterface $cache = null,
        string $roleClassname = RoleInterface::class
    ) {
        parent::__construct($hierarchy);

        $this->registry = $registry;
        $this->roleClassname = $roleClassname;
        $this->cache = $cache;
    }

    /**
     * Set event dispatcher.
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->eventDispatcher = $dispatcher;
    }

    public function getReachableRoleNames(array $roles): array
    {
        return $this->doGetReachableRoleNames(RoleUtil::formatNames($roles));
    }

    /**
     * Returns an array of all roles reachable by the given ones.
     *
     * @param string[] $roles  An array of roles
     * @param string   $suffix The role name suffix
     *
     * @return string[] An array of role instances
     */
    protected function doGetReachableRoleNames(array $roles, string $suffix = ''): array
    {
        if (0 === \count($roles)) {
            return $roles;
        }

        $item = null;
        $roles = $this->formatRoles($roles);
        $id = $this->getUniqueId($roles);

        if (null !== ($reachableRoles = $this->getCachedReachableRoleNames($id, $item))) {
            return $reachableRoles;
        }

        // build hierarchy
        /** @var string[] $reachableRoles */
        $reachableRoles = parent::getReachableRoleNames($roles);
        $isPermEnabled = true;

        if (null !== $this->eventDispatcher) {
            $event = new PreReachableRoleEvent($reachableRoles);
            $this->eventDispatcher->dispatch($event);
            $reachableRoles = $event->getReachableRoleNames();
            $isPermEnabled = $event->isPermissionEnabled();
        }

        return $this->getAllRoles($reachableRoles, $roles, $id, $item, $isPermEnabled, $suffix);
    }

    /**
     * Get the unique id.
     *
     * @param array $roleNames The role names
     */
    protected function getUniqueId(array $roleNames): string
    {
        return sha1(implode('|', $roleNames));
    }

    /**
     * Format the roles.
     *
     * @param string[] $roles The roles
     *
     * @return string[]
     */
    protected function formatRoles(array $roles): array
    {
        return $roles;
    }

    /**
     * Build the suffix of role.
     *
     * @param null|string $role The role
     */
    protected function buildRoleSuffix(?string $role): string
    {
        return '';
    }

    /**
     * Clean the role names.
     *
     * @param string[] $roles The role names
     *
     * @return string[]
     */
    protected function cleanRoleNames(array $roles): array
    {
        return $roles;
    }

    /**
     * Format the cleaned role name.
     *
     * @param string $name The role name
     */
    protected function formatCleanedRoleName(string $name): string
    {
        return $name;
    }

    /**
     * Get the reachable roles in cache if available.
     *
     * @param string                  $id   The cache id
     * @param null|CacheItemInterface $item The cache item variable passed by reference
     *
     * @return null|string[]
     *
     * @throws
     */
    private function getCachedReachableRoleNames(string $id, ?CacheItemInterface &$item): ?array
    {
        $roles = null;

        // find the hierarchy in execution cache
        if (isset($this->cacheExec[$id])) {
            return $this->cacheExec[$id];
        }

        // find the hierarchy in cache
        if (null !== $this->cache) {
            $item = $this->cache->getItem($id);
            $reachableRoles = $item->get();

            if (null !== $reachableRoles && $item->isHit()) {
                $roles = $reachableRoles;
            }
        }

        return $roles;
    }

    /**
     * Get all roles.
     *
     * @param string[]                $reachableRoles The reachable roles
     * @param string[]                $roles          The roles
     * @param string                  $id             The cache item id
     * @param null|CacheItemInterface $item           The cache item
     * @param bool                    $isPermEnabled  Check if the permission manager is enabled
     * @param string                  $suffix         The role name suffix
     *
     * @return string[]
     */
    private function getAllRoles(
        array $reachableRoles,
        array $roles,
        string $id,
        ?CacheItemInterface $item,
        bool $isPermEnabled,
        string $suffix = ''
    ): array {
        $reachableRoles = $this->findRecords($reachableRoles, $roles);
        $reachableRoles = $this->getCleanedRoles($reachableRoles, $suffix);

        // insert in cache
        if (null !== $this->cache && $item instanceof CacheItemInterface) {
            $item->set($reachableRoles);
            $this->cache->save($item);
        }

        $this->cacheExec[$id] = $reachableRoles;

        if (null !== $this->eventDispatcher) {
            $event = new PostReachableRoleEvent($reachableRoles, $isPermEnabled);
            $this->eventDispatcher->dispatch($event);
            $reachableRoles = $event->getReachableRoleNames();
        }

        return $reachableRoles;
    }

    /**
     * Find the roles in database.
     *
     * @param string[] $reachableRoles The reachable roles
     * @param string[] $roles          The role names
     *
     * @return string[]
     *
     * @throws
     */
    private function findRecords(array $reachableRoles, array $roles): array
    {
        $recordRoles = [];
        $om = ManagerUtils::getRequiredManager($this->registry, $this->roleClassname);
        $repo = $om->getRepository($this->roleClassname);

        $filters = SqlFilterUtil::findFilters($om, [], true);
        SqlFilterUtil::disableFilters($om, $filters);

        if (\count($roles) > 0) {
            $recordRoles = $repo->findBy(['name' => $this->cleanRoleNames($roles)]);
        }

        $loopReachableRoles = [$reachableRoles];

        /** @var RoleHierarchicalInterface $eRole */
        foreach ($recordRoles as $eRole) {
            $suffix = $this->buildRoleSuffix($roles[$eRole->getName()] ?? null);
            $subRoles = RoleUtil::formatNames($eRole->getChildren()->toArray());
            $loopReachableRoles[] = $this->doGetReachableRoleNames($subRoles, $suffix);
        }

        SqlFilterUtil::enableFilters($om, $filters);

        return array_merge(...$loopReachableRoles);
    }

    /**
     * Cleaning the double roles.
     *
     * @param string[] $reachableRoles The reachable roles
     * @param string   $suffix         The role name suffix
     *
     * @return string[]
     */
    private function getCleanedRoles(array $reachableRoles, string $suffix = ''): array
    {
        $existingRoles = [];
        $finalRoles = [];

        foreach ($reachableRoles as $role) {
            $name = $this->formatCleanedRoleName($role);

            if (!\in_array($name, $existingRoles, true)) {
                $rSuffix = 'ROLE_USER' !== $name && 'ORGANIZATION_ROLE_USER' !== $name ? $suffix : '';
                $existingRoles[] = $name;
                $finalRoles[] = $role.$rSuffix;
            }
        }

        return $finalRoles;
    }
}
