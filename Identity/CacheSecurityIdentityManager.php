<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Identity;

use Klipper\Component\Security\Event\AddSecurityIdentityEvent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Manager to retrieving security identities with caching.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class CacheSecurityIdentityManager extends SecurityIdentityManager implements CacheSecurityIdentityManagerInterface
{
    /**
     * @var null|CacheSecurityIdentityListenerInterface[]
     */
    private ?array $cacheIdentityListeners = null;

    private array $cacheExec = [];

    /**
     * Invalidate the execution cache.
     */
    public function invalidateCache(): void
    {
        $this->cacheExec = [];
    }

    public function getSecurityIdentities(?TokenInterface $token = null): array
    {
        if (null === $token) {
            return [];
        }

        $id = $this->buildId($token);

        if (isset($this->cacheExec[$id])) {
            return $this->cacheExec[$id];
        }

        return $this->cacheExec[$id] = parent::getSecurityIdentities($token);
    }

    /**
     * Build the unique identifier for execution cache.
     *
     * @param TokenInterface $token The token
     */
    protected function buildId(TokenInterface $token): string
    {
        $id = spl_object_hash($token);
        $listeners = $this->getCacheIdentityListeners();

        foreach ($listeners as $listener) {
            $id .= '_'.$listener->getCacheId();
        }

        return $id;
    }

    /**
     * Get the cache security identity listeners.
     *
     * @return CacheSecurityIdentityListenerInterface[]
     */
    protected function getCacheIdentityListeners(): array
    {
        if (null === $this->cacheIdentityListeners) {
            $this->cacheIdentityListeners = [];
            $listeners = $this->dispatcher->getListeners(AddSecurityIdentityEvent::class);

            foreach ($listeners as $listener) {
                $listener = \is_array($listener) && \count($listener) > 1 ? $listener[0] : $listener;

                if ($listener instanceof CacheSecurityIdentityListenerInterface) {
                    $this->cacheIdentityListeners[] = $listener;
                }
            }
        }

        return $this->cacheIdentityListeners;
    }
}
