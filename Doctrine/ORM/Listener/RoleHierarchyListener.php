<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Doctrine\ORM\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
use Klipper\Component\Cache\Adapter\AdapterInterface;
use Klipper\Component\Security\Identity\CacheSecurityIdentityManagerInterface;
use Klipper\Component\Security\Identity\SecurityIdentityManagerInterface;
use Klipper\Component\Security\Model\GroupInterface;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\Model\RoleHierarchicalInterface;
use Klipper\Component\Security\Model\Traits\GroupableInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Invalidate the role hierarchy cache when users, roles or groups is inserted,
 * updated or deleted.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RoleHierarchyListener implements EventSubscriber
{
    protected SecurityIdentityManagerInterface $sim;

    protected ?CacheItemPoolInterface $cache;

    protected ?OrganizationalContextInterface $context;

    /**
     * @param SecurityIdentityManagerInterface    $sim     The security identity manager
     * @param null|CacheItemPoolInterface         $cache   The cache
     * @param null|OrganizationalContextInterface $context The organizational context
     */
    public function __construct(
        SecurityIdentityManagerInterface $sim,
        ?CacheItemPoolInterface $cache = null,
        ?OrganizationalContextInterface $context = null
    ) {
        $this->sim = $sim;
        $this->cache = $cache;
        $this->context = $context;
    }

    public function getSubscribedEvents(): array
    {
        return [Events::onFlush];
    }

    /**
     * On flush action.
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $uow = $args->getObjectManager()->getUnitOfWork();
        $collection = $this->getAllCollections($uow);
        $invalidates = [];

        // check all scheduled insertions
        foreach ($collection as $object) {
            $invalidate = $this->invalidateCache($uow, $object);

            if (\is_string($invalidate)) {
                $invalidates[] = $invalidate;
            }
        }

        $this->flushCache(array_unique($invalidates));
    }

    /**
     * Flush the cache.
     *
     * @param array $invalidates The prefix must be invalidated
     */
    protected function flushCache(array $invalidates): void
    {
        if (\count($invalidates) > 0) {
            if ($this->cache instanceof AdapterInterface && null !== $this->context) {
                $this->cache->clearByPrefixes($invalidates);
            } elseif (null !== $this->cache) {
                $this->cache->clear();
            }

            if ($this->sim instanceof CacheSecurityIdentityManagerInterface) {
                $this->sim->invalidateCache();
            }
        }
    }

    /**
     * Get the merged collection of all scheduled collections.
     *
     * @param UnitOfWork $uow The unit of work
     */
    protected function getAllCollections(UnitOfWork $uow): array
    {
        return array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates(),
            $uow->getScheduledEntityDeletions(),
            $uow->getScheduledCollectionUpdates(),
            $uow->getScheduledCollectionDeletions()
        );
    }

    /**
     * Check if the role hierarchy cache must be invalidated.
     *
     * @param UnitOfWork $uow    The unit of work
     * @param object     $object The object
     *
     * @return false|string
     */
    protected function invalidateCache(UnitOfWork $uow, object $object)
    {
        if ($this->isCacheableObject($object)) {
            return $this->invalidateCacheableObject($uow, $object);
        }

        if ($object instanceof PersistentCollection && $this->isRequireAssociation($object->getMapping())) {
            return $this->getPrefix($object->getOwner());
        }

        return false;
    }

    /**
     * Check if the object is cacheable or not.
     *
     * @param object $object The object
     */
    protected function isCacheableObject(object $object): bool
    {
        return $object instanceof UserInterface || $object instanceof RoleHierarchicalInterface || $object instanceof GroupInterface || $object instanceof OrganizationUserInterface;
    }

    /**
     * Check if the association must be flush the cache.
     *
     * @param array $mapping The mapping
     *
     * @throws
     */
    protected function isRequireAssociation(array $mapping): bool
    {
        $ref = new \ReflectionClass($mapping['sourceEntity']);

        if ('children' === $mapping['fieldName']
                && \in_array(RoleHierarchicalInterface::class, $ref->getInterfaceNames(), true)) {
            return true;
        }

        if ('groups' === $mapping['fieldName']
                && \in_array(GroupableInterface::class, $ref->getInterfaceNames(), true)) {
            return true;
        }

        return false;
    }

    /**
     * Get the cache prefix key.
     */
    protected function getPrefix(object $object): string
    {
        $id = 'user';

        if (method_exists($object, 'getOrganization')) {
            $org = $object->getOrganization();

            if ($org instanceof OrganizationInterface) {
                $id = (string) $org->getId();
            }
        }

        return $id.'__';
    }

    /**
     * Check if the object cache must be invalidated.
     *
     * @param UnitOfWork $uow    The unit of work
     * @param object     $object The object
     *
     * @return bool|string
     */
    private function invalidateCacheableObject(UnitOfWork $uow, object $object)
    {
        $fields = array_keys($uow->getEntityChangeSet($object));
        $checkFields = ['roles'];

        if ($object instanceof RoleHierarchicalInterface || $object instanceof OrganizationUserInterface) {
            $checkFields = array_merge($checkFields, ['name']);
        }

        foreach ($fields as $field) {
            if (\in_array($field, $checkFields, true)) {
                return $this->getPrefix($object);
            }
        }

        return false;
    }
}
