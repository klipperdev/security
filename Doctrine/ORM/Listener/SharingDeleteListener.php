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
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Klipper\Component\Security\Identity\SubjectIdentity;
use Klipper\Component\Security\Model\SharingInterface;
use Klipper\Component\Security\Sharing\SharingManagerInterface;

/**
 * Doctrine ORM listener for sharing filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingDeleteListener implements EventSubscriber
{
    private SharingManagerInterface $sharingManager;

    private array $deleteSharingSubjects = [];

    private array $deleteSharingIdentities = [];

    public function __construct(SharingManagerInterface $sharingManager)
    {
        $this->sharingManager = $sharingManager;
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
     * On flush action.
     *
     * @param OnFlushEventArgs $args The event
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $uow = $args->getEntityManager()->getUnitOfWork();

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $class = ClassUtils::getClass($entity);

            if ($this->sharingManager->hasSubjectConfig($class)) {
                $subject = SubjectIdentity::fromObject($entity);
                $this->deleteSharingSubjects[$subject->getType()][] = $subject->getIdentifier();
            } elseif ($this->sharingManager->hasIdentityConfig($class)) {
                $subject = SubjectIdentity::fromObject($entity);
                $this->deleteSharingIdentities[$subject->getType()][] = $subject->getIdentifier();
            }
        }
    }

    /**
     * Post flush action.
     *
     * @param PostFlushEventArgs $args The event
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        if (!empty($this->deleteSharingSubjects) || !empty($this->deleteSharingIdentities)) {
            $this->buildDeleteQuery($args->getEntityManager())->execute();
        }

        $this->deleteSharingSubjects = [];
        $this->deleteSharingIdentities = [];
    }

    /**
     * Build the delete query.
     *
     * @param EntityManagerInterface $em The entity manager
     *
     * @return Query
     */
    private function buildDeleteQuery(EntityManagerInterface $em): AbstractQuery
    {
        $qb = $em->createQueryBuilder()
            ->delete(SharingInterface::class, 's')
        ;

        $this->buildCriteria($qb, $this->deleteSharingSubjects, 'subjectClass', 'subjectId');
        $this->buildCriteria($qb, $this->deleteSharingIdentities, 'identityClass', 'identityName');

        return $qb->getQuery();
    }

    /**
     * Build the query criteria.
     *
     * @param QueryBuilder $qb         The query builder
     * @param array        $mapIds     The map of classname and identifiers
     * @param string       $fieldClass The name of field class
     * @param string       $fieldId    The name of field identifier
     */
    private function buildCriteria(QueryBuilder $qb, array $mapIds, string $fieldClass, string $fieldId): void
    {
        if (!empty($mapIds)) {
            $where = '';
            $parameters = [];
            $i = 0;

            foreach ($mapIds as $type => $identifiers) {
                $pClass = $fieldClass.'_'.$i;
                $pIdentifiers = $fieldId.'s_'.$i;
                $parameters[$pClass] = $type;
                $parameters[$pIdentifiers] = $identifiers;
                $where .= '' === $where ? '' : ' OR ';
                $where .= sprintf('(s.%s = :%s AND s.%s IN (:%s))', $fieldClass, $pClass, $fieldId, $pIdentifiers);
                ++$i;
            }

            $qb->andWhere($where);

            foreach ($parameters as $key => $identifiers) {
                $qb->setParameter($key, $identifiers);
            }
        }
    }
}
