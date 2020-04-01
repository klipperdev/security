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

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Klipper\Component\Security\Exception\AccessDeniedException;
use Klipper\Component\Security\ObjectFilter\ObjectFilterInterface;
use Klipper\Component\Security\Token\ConsoleToken;

/**
 * This class listens to all database activity and automatically adds constraints as permissions.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ObjectFilterListener extends AbstractPermissionListener
{
    /**
     * @var ObjectFilterInterface
     */
    protected $objectFilter;

    /**
     * Specifies the list of listened events.
     *
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::postLoad,
            Events::onFlush,
            Events::postFlush,
        ];
    }

    /**
     * This method is executed after every load that doctrine performs.
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args): void
    {
        $token = $this->getTokenStorage()->getToken();

        if (null === $token || $token instanceof ConsoleToken || !$this->getPermissionManager()->isEnabled()) {
            return;
        }

        $object = $args->getEntity();
        $this->getObjectFilter()->filter($object);
    }

    /**
     * This method is executed each time doctrine does a flush on an entity manager.
     *
     * @param OnFlushEventArgs $args The event
     *
     * @throws AccessDeniedException When insufficient privilege for called action
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $token = $this->getTokenStorage()->getToken();

        if (null === $token || $token instanceof ConsoleToken || !$this->getPermissionManager()->isEnabled()) {
            return;
        }

        $uow = $args->getEntityManager()->getUnitOfWork();
        $this->getObjectFilter()->beginTransaction();

        $this->checkAllScheduledByAction($uow->getScheduledEntityInsertions(), 'create');
        $this->checkAllScheduledByAction($uow->getScheduledEntityUpdates(), 'edit');
        $this->checkAllScheduledByAction($uow->getScheduledEntityDeletions(), 'delete');

        $this->getObjectFilter()->commit();
    }

    /**
     * Set the object filter.
     *
     * @param ObjectFilterInterface $objectFilter The object filter
     *
     * @return static
     */
    public function setObjectFilter(ObjectFilterInterface $objectFilter): self
    {
        $this->objectFilter = $objectFilter;

        return $this;
    }

    /**
     * Get the Object Filter.
     *
     * @throws
     *
     * @return ObjectFilterInterface
     */
    protected function getObjectFilter(): ObjectFilterInterface
    {
        $this->init();

        return $this->objectFilter;
    }

    /**
     * Check all scheduled objects by action type.
     *
     * @param object[] $objects The objects
     * @param string   $action  The action name
     */
    protected function checkAllScheduledByAction(array $objects, string $action): void
    {
        foreach ($objects as $object) {
            $this->postResetPermissions[] = $object;

            if ('delete' !== $action) {
                $this->getObjectFilter()->restore($object);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getInitProperties(): array
    {
        return [
            'tokenStorage' => 'setTokenStorage',
            'permissionManager' => 'setPermissionManager',
            'objectFilter' => 'setObjectFilter',
        ];
    }
}
