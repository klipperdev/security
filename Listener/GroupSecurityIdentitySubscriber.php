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

use Klipper\Component\Security\Event\AddSecurityIdentityEvent;
use Klipper\Component\Security\Identity\GroupSecurityIdentity;
use Klipper\Component\Security\Identity\IdentityUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for add group security identity from token.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class GroupSecurityIdentitySubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AddSecurityIdentityEvent::class => ['addGroupSecurityIdentities', 0],
        ];
    }

    /**
     * Add group security identities.
     *
     * @param AddSecurityIdentityEvent $event The event
     */
    public function addGroupSecurityIdentities(AddSecurityIdentityEvent $event): void
    {
        try {
            $sids = $event->getSecurityIdentities();
            $sids = IdentityUtils::merge(
                $sids,
                GroupSecurityIdentity::fromToken($event->getToken())
            );
            $event->setSecurityIdentities($sids);
        } catch (\InvalidArgumentException $e) {
            // ignore
        }
    }
}
