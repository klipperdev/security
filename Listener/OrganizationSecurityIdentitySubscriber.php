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
use Klipper\Component\Security\Identity\CacheSecurityIdentityListenerInterface;
use Klipper\Component\Security\Identity\IdentityUtils;
use Klipper\Component\Security\Identity\OrganizationSecurityIdentity;
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Subscriber for add organization security identity from token.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationSecurityIdentitySubscriber implements EventSubscriberInterface, CacheSecurityIdentityListenerInterface
{
    private RoleHierarchyInterface $roleHierarchy;

    private OrganizationalContextInterface $context;

    /**
     * @param RoleHierarchyInterface         $roleHierarchy The role hierarchy
     * @param OrganizationalContextInterface $context       The organizational context
     */
    public function __construct(
        RoleHierarchyInterface $roleHierarchy,
        OrganizationalContextInterface $context
    ) {
        $this->roleHierarchy = $roleHierarchy;
        $this->context = $context;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AddSecurityIdentityEvent::class => ['addOrganizationSecurityIdentities', 0],
        ];
    }

    public function getCacheId(): string
    {
        $org = $this->context->getCurrentOrganization();

        return null !== $org
            ? 'org'.$org->getId()
            : '';
    }

    /**
     * Add organization security identities.
     *
     * @param AddSecurityIdentityEvent $event The event
     */
    public function addOrganizationSecurityIdentities(AddSecurityIdentityEvent $event): void
    {
        try {
            $sids = $event->getSecurityIdentities();
            $sids = IdentityUtils::merge(
                $sids,
                OrganizationSecurityIdentity::fromToken(
                    $event->getToken(),
                    $this->context,
                    $this->roleHierarchy
                )
            );
            $event->setSecurityIdentities($sids);
        } catch (\InvalidArgumentException $e) {
            // ignore
        }
    }
}
