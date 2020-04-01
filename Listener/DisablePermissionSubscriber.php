<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Listener;

use Klipper\Component\Security\Event\AbstractEditableSecurityEvent;
use Klipper\Component\Security\Event\AbstractSecurityEvent;
use Klipper\Component\Security\Event\PostReachableRoleEvent;
use Klipper\Component\Security\Event\PostSecurityIdentityEvent;
use Klipper\Component\Security\Event\PreReachableRoleEvent;
use Klipper\Component\Security\Event\PreSecurityIdentityEvent;
use Klipper\Component\Security\Permission\PermissionManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener for disable/re-enable the permission manager.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class DisablePermissionSubscriber implements EventSubscriberInterface
{
    /**
     * @var PermissionManagerInterface
     */
    protected $permManager;

    /**
     * Constructor.
     *
     * @param PermissionManagerInterface $permManager The permission manager
     */
    public function __construct(PermissionManagerInterface $permManager)
    {
        $this->permManager = $permManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PreSecurityIdentityEvent::class => ['disablePermissionManager', -255],
            PreReachableRoleEvent::class => ['disablePermissionManager', -255],
            PostSecurityIdentityEvent::class => ['enablePermissionManager', 255],
            PostReachableRoleEvent::class => ['enablePermissionManager', 255],
        ];
    }

    /**
     * Disable the permission manager.
     *
     * @param AbstractEditableSecurityEvent $event The event
     */
    public function disablePermissionManager(AbstractEditableSecurityEvent $event): void
    {
        $event->setPermissionEnabled($this->permManager->isEnabled());
        $this->permManager->setEnabled(false);
    }

    /**
     * Enable the permission manager.
     *
     * @param AbstractSecurityEvent $event The event
     */
    public function enablePermissionManager(AbstractSecurityEvent $event): void
    {
        $this->permManager->setEnabled($event->isPermissionEnabled());
    }
}
