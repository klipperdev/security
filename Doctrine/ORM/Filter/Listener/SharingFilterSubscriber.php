<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Doctrine\ORM\Filter\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Klipper\Component\DoctrineExtensions\Filter\Listener\AbstractFilterSubscriber;
use Klipper\Component\Security\Doctrine\ORM\Filter\SharingFilter;
use Klipper\Component\Security\Event\SetCurrentOrganizationEvent;
use Klipper\Component\Security\Event\SharingDisabledEvent;
use Klipper\Component\Security\Event\SharingEnabledEvent;
use Klipper\Component\Security\Identity\IdentityUtils;
use Klipper\Component\Security\Identity\SecurityIdentityInterface;
use Klipper\Component\Security\Identity\SecurityIdentityManagerInterface;
use Klipper\Component\Security\Model\SharingInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\Security\Sharing\SharingManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Sharing filter listener.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingFilterSubscriber extends AbstractFilterSubscriber
{
    protected EventDispatcherInterface $dispatcher;

    protected TokenStorageInterface $tokenStorage;

    protected SecurityIdentityManagerInterface $sidManager;

    protected SharingManagerInterface $sharingManager;

    protected string $sharingClass;

    /**
     * @param EntityManagerInterface           $entityManager  The entity manager
     * @param EventDispatcherInterface         $dispatcher     The event dispatcher
     * @param TokenStorageInterface            $tokenStorage   The token storage
     * @param SecurityIdentityManagerInterface $sidManager     The security identity manager
     * @param SharingManagerInterface          $sharingManager The sharing manager
     * @param string                           $sharingClass   The classname of sharing model
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
        TokenStorageInterface $tokenStorage,
        SecurityIdentityManagerInterface $sidManager,
        SharingManagerInterface $sharingManager,
        string $sharingClass = SharingInterface::class
    ) {
        parent::__construct($entityManager);

        $this->dispatcher = $dispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->sidManager = $sidManager;
        $this->sharingManager = $sharingManager;
        $this->sharingClass = $sharingClass;
    }

    public static function getSubscribedEvents(): array
    {
        return array_merge(parent::getSubscribedEvents(), [
            SetCurrentOrganizationEvent::class => [
                ['onEvent', 0],
            ],
            SharingEnabledEvent::class => [
                ['onSharingManagerChange', 0],
            ],
            SharingDisabledEvent::class => [
                ['onSharingManagerChange', 0],
            ],
        ]);
    }

    /**
     * Action when the sharing manager is enabled or disabled.
     */
    public function onSharingManagerChange(): void
    {
        if (null !== ($filter = $this->getFilter())) {
            $filter->setParameter('sharing_manager_enabled', $this->sharingManager->isEnabled(), 'boolean');
        }
    }

    protected function supports(): string
    {
        return SharingFilter::class;
    }

    protected function injectParameters(SQLFilter $filter): void
    {
        /* @var SharingFilter $filter */
        $filter->setEventDispatcher($this->dispatcher);
        $filter->setSharingManager($this->sharingManager);
        $filter->setSharingClass($this->sharingClass);
        $sids = $this->buildSecurityIdentities();

        $filter->setParameter('has_security_identities', !empty($sids), 'boolean');
        $filter->setParameter('map_security_identities', $this->getMapSecurityIdentities($sids), 'array');
        $filter->setParameter('user_id', $this->getUserId());
        $filter->setParameter('sharing_manager_enabled', $this->sharingManager->isEnabled(), 'boolean');
    }

    /**
     * Build the security identities.
     *
     * @return SecurityIdentityInterface[]
     */
    private function buildSecurityIdentities(): array
    {
        $tSids = $this->sidManager->getSecurityIdentities($this->tokenStorage->getToken());
        $sids = [];

        foreach ($tSids as $sid) {
            if (IdentityUtils::isValid($sid)) {
                $sids[] = $sid;
            }
        }

        return $sids;
    }

    /**
     * Get the map of the security identities.
     *
     * @param SecurityIdentityInterface[] $sids The security identities
     */
    private function getMapSecurityIdentities(array $sids): array
    {
        $connection = $this->entityManager->getConnection();
        $mapSids = [];

        foreach ($sids as $sid) {
            $sidType = $sid->getType();
            $sidType = $this->sharingManager->hasIdentityConfig($sidType) ? $this->sharingManager->getIdentityConfig($sidType)->getType() : $sidType;
            $mapSids[$sidType][] = $connection->quote($sid->getIdentifier());
        }

        foreach ($mapSids as $type => $ids) {
            $mapSids[$type] = implode(', ', $ids);
        }

        return $mapSids;
    }

    /**
     * Get the current user id.
     *
     * @return null|int|string
     */
    private function getUserId()
    {
        $id = null;

        if (null !== $this->tokenStorage && null !== $this->tokenStorage->getToken()) {
            $user = $this->tokenStorage->getToken()->getUser();

            if ($user instanceof UserInterface) {
                $id = $user->getId();
            }
        }

        return $id;
    }
}
