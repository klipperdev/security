<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Fixtures\Listener;

use Klipper\Component\Security\Event\AddSecurityIdentityEvent;
use Klipper\Component\Security\Identity\CacheSecurityIdentityListenerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MockCacheSecurityIdentitySubscriber implements EventSubscriberInterface, CacheSecurityIdentityListenerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AddSecurityIdentityEvent::class => ['onAddIdentity', 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheId(): string
    {
        return 'cache_id';
    }

    /**
     * Action on add identity.
     */
    public function onAddIdentity(): void
    {
        // do nothing
    }
}
