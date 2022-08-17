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
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Klipper\Component\Security\Exception\AccessDeniedException;
use Klipper\Component\Security\ObjectFilter\ObjectFilterInterface;
use Klipper\Component\Security\Permission\PermissionManagerInterface;
use Klipper\Component\Security\Token\ConsoleToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * This class listens to all database activity and automatically adds constraints as permissions.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ObjectFilterListener implements EventSubscriber
{
    private PermissionManagerInterface $permissionManager;

    private TokenStorageInterface $tokenStorage;

    private ObjectFilterInterface $objectFilter;

    private array $postResetPermissions = [];

    public function __construct(
        PermissionManagerInterface $permissionManager,
        TokenStorageInterface $tokenStorage,
        ObjectFilterInterface $objectFilter
    ) {
        $this->permissionManager = $permissionManager;
        $this->tokenStorage = $tokenStorage;
        $this->objectFilter = $objectFilter;
    }

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
     */
    public function postLoad(LifecycleEventArgs $args): void
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token || $token instanceof ConsoleToken || !$this->permissionManager->isEnabled()) {
            return;
        }

        $object = $args->getEntity();
        $this->objectFilter->filter($object);
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
        $token = $this->tokenStorage->getToken();

        if (null === $token || $token instanceof ConsoleToken || !$this->permissionManager->isEnabled()) {
            return;
        }

        $uow = $args->getObjectManager()->getUnitOfWork();
        $this->objectFilter->beginTransaction();

        $this->checkAllScheduledByAction($uow->getScheduledEntityInsertions(), 'create');
        $this->checkAllScheduledByAction($uow->getScheduledEntityUpdates(), 'edit');
        $this->checkAllScheduledByAction($uow->getScheduledEntityDeletions(), 'delete');

        $this->objectFilter->commit();
    }

    /**
     * Reset the preloaded permissions used for the insertions.
     */
    public function postFlush(): void
    {
        $this->permissionManager->resetPreloadPermissions($this->postResetPermissions);
        $this->postResetPermissions = [];
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
                $this->objectFilter->restore($object);
            }
        }
    }
}
