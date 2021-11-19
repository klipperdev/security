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
use Klipper\Component\Security\Exception\OrganizationUserNotFoundException;
use Klipper\Component\Security\Identity\CacheSecurityIdentityListenerInterface;
use Klipper\Component\Security\Identity\IdentityUtils;
use Klipper\Component\Security\Identity\OrganizationSecurityIdentity;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Klipper\Component\Security\Organizational\OrganizationUserProviderInterface;
use Klipper\Component\Security\Organizational\StaticOrganizationalContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Subscriber for add organization security identity from token.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationSecurityIdentitySubscriber implements EventSubscriberInterface, CacheSecurityIdentityListenerInterface
{
    private RoleHierarchyInterface $roleHierarchy;

    private TokenStorageInterface $tokenStorage;

    private OrganizationalContextInterface $context;

    private ?OrganizationUserProviderInterface $organizationUserProvider;

    /**
     * @param RoleHierarchyInterface                 $roleHierarchy            The role hierarchy
     * @param TokenStorageInterface                  $tokenStorage             The token storage
     * @param OrganizationalContextInterface         $context                  The organizational context
     * @param null|OrganizationUserProviderInterface $organizationUserProvider The organization user provider
     */
    public function __construct(
        RoleHierarchyInterface $roleHierarchy,
        TokenStorageInterface $tokenStorage,
        OrganizationalContextInterface $context,
        ?OrganizationUserProviderInterface $organizationUserProvider = null
    ) {
        $this->roleHierarchy = $roleHierarchy;
        $this->tokenStorage = $tokenStorage;
        $this->context = $context;
        $this->organizationUserProvider = $organizationUserProvider;
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
            $token = $event->getToken();
            $sids = $event->getSecurityIdentities();
            $sids = IdentityUtils::merge(
                $sids,
                OrganizationSecurityIdentity::fromToken(
                    $token,
                    $this->getContext($token),
                    $this->roleHierarchy
                )
            );
            $event->setSecurityIdentities($sids);
        } catch (\InvalidArgumentException $e) {
            // ignore
        }
    }

    private function getContext(TokenInterface $token): OrganizationalContextInterface
    {
        $user = $token->getUser();
        $org = $this->context->getCurrentOrganization();

        if ($user instanceof UserInterface && null !== $org && null !== $this->organizationUserProvider && $this->tokenStorage->getToken() !== $token) {
            try {
                $orgUser = $this->organizationUserProvider->loadOrganizationUserByUser($org, $user);

                return null === $orgUser ? $this->context : new StaticOrganizationalContext(
                    $org,
                    $orgUser
                );
            } catch (OrganizationUserNotFoundException $e) {
                return $this->context;
            }
        }

        return $this->context;
    }
}
