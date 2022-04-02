<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Doctrine\ORM\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Klipper\Component\DoctrineExtensions\Filter\AbstractFilter;
use Klipper\Component\Security\Doctrine\DoctrineSharingVisibilities;
use Klipper\Component\Security\Doctrine\ORM\Event\AbstractGetFilterEvent;
use Klipper\Component\Security\Doctrine\ORM\Event\GetNoneFilterEvent;
use Klipper\Component\Security\Identity\SubjectUtils;
use Klipper\Component\Security\Sharing\SharingManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Sharing filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingFilter extends AbstractFilter
{
    protected ?SharingManagerInterface $sm = null;

    protected ?EventDispatcherInterface $dispatcher = null;

    protected ?string $sharingClass = null;

    /**
     * Set the sharing manager.
     *
     * @param SharingManagerInterface $sharingManager The sharing manager
     *
     * @return static
     */
    public function setSharingManager(SharingManagerInterface $sharingManager): self
    {
        $this->sm = $sharingManager;

        return $this;
    }

    /**
     * Set the event dispatcher.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher
     *
     * @return static
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher): self
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * Set the class name of the sharing model.
     *
     * @param string $class The class name of sharing model
     *
     * @return static
     */
    public function setSharingClass(string $class): self
    {
        $this->sharingClass = $class;

        return $this;
    }

    public function doAddFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        $visibility = $this->sm->getSharingVisibility(SubjectUtils::getSubjectIdentity($targetEntity->getName()));
        $eventClass = DoctrineSharingVisibilities::$classMap[$visibility] ?? GetNoneFilterEvent::class;

        /** @var AbstractGetFilterEvent $event */
        $event = new $eventClass($this, $this->getEntityManager(), $targetEntity, $targetTableAlias, $this->sharingClass);
        $this->dispatcher->dispatch($event);

        return $event->getFilterConstraint();
    }

    /**
     * @throws
     */
    protected function supports(ClassMetadata $targetEntity): bool
    {
        $subject = SubjectUtils::getSubjectIdentity($targetEntity->getName());

        return $this->hasParameter('has_security_identities')
            && $this->hasParameter('map_security_identities')
            && $this->hasParameter('user_id')
            && $this->hasParameter('sharing_manager_enabled')
            && $this->getRealParameter('sharing_manager_enabled')
            && null !== $this->dispatcher
            && null !== $this->sm
            && null !== $this->sharingClass
            && $this->sm->hasSharingVisibility($subject);
    }
}
