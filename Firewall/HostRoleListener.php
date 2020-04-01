<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Firewall;

use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Inject the host role in security identity manager.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class HostRoleListener extends AbstractRoleListener
{
    /**
     * Handles anonymous authentication.
     *
     * @param RequestEvent $event A RequestEvent instance
     */
    public function __invoke(RequestEvent $event): void
    {
        if ($this->isEnabled()) {
            $hostRole = $this->getHostRole($event);

            if (null !== $hostRole) {
                $this->sidManager->addSpecialRole($hostRole);
            }
        }
    }

    /**
     * Get the host role.
     *
     * @param RequestEvent $event A RequestEvent instance
     */
    protected function getHostRole(RequestEvent $event): ?string
    {
        $hostRole = null;
        $hostname = $event->getRequest()->getHttpHost();

        foreach ($this->config as $hostPattern => $role) {
            if ($this->isValid($hostPattern, $hostname)) {
                $hostRole = $role;

                break;
            }
        }

        return $hostRole;
    }

    /**
     * Check if the hostname matching with the host pattern.
     *
     * @param string $pattern  The shell pattern or regex pattern starting and ending with a slash
     * @param string $hostname The host name
     */
    private function isValid(string $pattern, string $hostname): bool
    {
        return 0 === strpos($pattern, '/') && (1 + strrpos($pattern, '/')) === \strlen($pattern)
            ? (bool) preg_match($pattern, $hostname)
            : fnmatch($pattern, $hostname);
    }
}
