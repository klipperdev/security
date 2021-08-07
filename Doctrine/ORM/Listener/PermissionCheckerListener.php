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
use Klipper\Component\Security\Exception\AccessDeniedException;
use Klipper\Component\Security\Permission\PermissionManagerInterface;
use Klipper\Component\Security\Permission\PermVote;
use Klipper\Component\Security\Token\ConsoleToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * This class listens to all database activity and automatically adds constraints as permissions.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PermissionCheckerListener implements EventSubscriber
{
    private PermissionManagerInterface $permissionManager;

    private TokenStorageInterface $tokenStorage;

    private AuthorizationCheckerInterface $authChecker;

    private array $postResetPermissions = [];

    public function __construct(
        PermissionManagerInterface $permissionManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authChecker
    ) {
        $this->permissionManager = $permissionManager;
        $this->tokenStorage = $tokenStorage;
        $this->authChecker = $authChecker;
    }

    /**
     * Specifies the list of listened events.
     *
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
            Events::postFlush,
        ];
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

        if (null === $token
                || $token instanceof ConsoleToken
                || !$this->permissionManager->isEnabled()) {
            return;
        }

        $uow = $args->getEntityManager()->getUnitOfWork();
        $createEntities = $uow->getScheduledEntityInsertions();
        $updateEntities = $uow->getScheduledEntityUpdates();
        $deleteEntities = $uow->getScheduledEntityDeletions();

        $this->postResetPermissions = array_merge($createEntities, $updateEntities, $deleteEntities);
        $this->permissionManager->preloadPermissions($this->postResetPermissions);

        $this->checkAllScheduledByAction($createEntities, 'create');
        $this->checkAllScheduledByAction($updateEntities, 'update');
        $this->checkAllScheduledByAction($deleteEntities, 'delete');
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
            if (!$this->authChecker->isGranted(new PermVote($action), $object)) {
                throw new AccessDeniedException('Insufficient privilege to '.$action.' the entity');
            }
        }
    }
}
