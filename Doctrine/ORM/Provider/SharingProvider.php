<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Doctrine\ORM\Provider;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Klipper\Component\DoctrineExtra\Util\ManagerUtils;
use Klipper\Component\Security\Exception\InvalidArgumentException;
use Klipper\Component\Security\Identity\IdentityUtils;
use Klipper\Component\Security\Identity\SecurityIdentityInterface;
use Klipper\Component\Security\Identity\SecurityIdentityManagerInterface;
use Klipper\Component\Security\Identity\SubjectIdentityInterface;
use Klipper\Component\Security\Model\RoleInterface;
use Klipper\Component\Security\Model\SharingInterface;
use Klipper\Component\Security\Sharing\SharingManagerInterface;
use Klipper\Component\Security\Sharing\SharingProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * The Doctrine Orm Sharing Provider.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingProvider implements SharingProviderInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var null|EntityRepository
     */
    protected $roleRepo;

    /**
     * @var null|EntityRepository
     */
    protected $sharingRepo;

    /**
     * @var SharingManagerInterface
     */
    protected $sharingManager;

    /**
     * @var SecurityIdentityManagerInterface
     */
    protected $sidManager;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * Constructor.
     *
     * @param ManagerRegistry                  $doctrine     The doctrine
     * @param SecurityIdentityManagerInterface $sidManager   The security identity manager
     * @param TokenStorageInterface            $tokenStorage The token storage
     */
    public function __construct(
        ManagerRegistry $doctrine,
        SecurityIdentityManagerInterface $sidManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->doctrine = $doctrine;
        $this->sidManager = $sidManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function setSharingManager(SharingManagerInterface $sharingManager): self
    {
        $this->sharingManager = $sharingManager;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissionRoles(array $roles): array
    {
        if (empty($roles)) {
            return [];
        }

        $qb = $this->getRoleRepository()->createQueryBuilder('r')
            ->addSelect('p')
            ->leftJoin('r.permissions', 'p')
        ;

        return $qb
            ->where('UPPER(r.name) IN (:roles)')
            ->setParameter('roles', $roles)
            ->orderBy('p.class', 'asc')
            ->addOrderBy('p.field', 'asc')
            ->addOrderBy('p.operation', 'asc')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getSharingEntries(array $subjects, $sids = null): array
    {
        if (empty($subjects) || null === $this->getSharingRepository()) {
            return [];
        }

        $sids = $this->getSecurityIdentities($sids);
        $qb = $this->getSharingRepository()->createQueryBuilder('s')
            ->addSelect('p')
            ->leftJoin('s.permissions', 'p')
        ;

        return $this->addWhereForSharing($qb, $subjects, $sids)
            ->andWhere('s.enabled = TRUE AND (s.startedAt IS NULL OR s.startedAt <= CURRENT_TIMESTAMP()) AND (s.endedAt IS NULL OR s.endedAt >= CURRENT_TIMESTAMP())')
            ->orderBy('p.class', 'asc')
            ->addOrderBy('p.field', 'asc')
            ->addOrderBy('p.operation', 'asc')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function renameIdentity(string $type, string $oldName, string $newName): self
    {
        $this->getSharingRepository()->createQueryBuilder('s')
            ->update($this->getSharingRepository()->getClassName(), 's')
            ->set('s.identityName', ':newName')
            ->where('s.identityClass = :type')
            ->andWhere('s.identityName = :oldName')
            ->setParameter('type', $type)
            ->setParameter('oldName', $oldName)
            ->setParameter('newName', $newName)
            ->getQuery()
            ->execute()
        ;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIdentity(string $type, string $name): self
    {
        $this->getSharingRepository()->createQueryBuilder('s')
            ->delete($this->getSharingRepository()->getClassName(), 's')
            ->where('s.identityClass = :type')
            ->andWhere('s.identityName = :name')
            ->setParameter('type', $type)
            ->setParameter('name', $name)
            ->getQuery()
            ->execute()
        ;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function deletes(array $ids): self
    {
        if (!empty($ids)) {
            $this->getSharingRepository()->createQueryBuilder('s')
                ->delete($this->getSharingRepository()->getClassName(), 's')
                ->where('s.id IN (:ids)')
                ->setParameter('ids', $ids)
                ->getQuery()
                ->execute()
            ;
        }

        return $this;
    }

    /**
     * Add where condition for sharing.
     *
     * @param QueryBuilder                $qb       The query builder
     * @param SubjectIdentityInterface[]  $subjects The subjects
     * @param SecurityIdentityInterface[] $sids     The security identities
     */
    private function addWhereForSharing(QueryBuilder $qb, array $subjects, array $sids): QueryBuilder
    {
        $where = '';
        $parameters = [];

        foreach ($subjects as $i => $subject) {
            $class = 'subject'.$i.'_class';
            $id = 'subject'.$i.'_id';
            $parameters[$class] = $subject->getType();
            $parameters[$id] = $subject->getIdentifier();
            $where .= '' === $where ? '' : ' OR ';
            $where .= sprintf('(s.subjectClass = :%s AND s.subjectId = :%s)', $class, $id);
        }

        $qb->where($where);

        foreach ($parameters as $key => $value) {
            $qb->setParameter($key, $value);
        }

        return $this->addWhereSecurityIdentitiesForSharing($qb, $sids);
    }

    /**
     * Add security identities where condition for sharing.
     *
     * @param QueryBuilder                $qb   The query builder
     * @param SecurityIdentityInterface[] $sids The security identities
     */
    private function addWhereSecurityIdentitiesForSharing(QueryBuilder $qb, array $sids): QueryBuilder
    {
        if (!empty($sids) && !empty($groupSids = $this->groupSecurityIdentities($sids))) {
            $where = '';
            $parameters = [];
            $i = 0;

            foreach ($groupSids as $type => $identifiers) {
                $qClass = 'sid'.$i.'_class';
                $qIdentifiers = 'sid'.$i.'_ids';
                $parameters[$qClass] = $type;
                $parameters[$qIdentifiers] = $identifiers;
                $where .= '' === $where ? '' : ' OR ';
                $where .= sprintf('(s.identityClass = :%s AND s.identityName IN (:%s))', $qClass, $qIdentifiers);
                ++$i;
            }

            $qb->andWhere($where);

            foreach ($parameters as $key => $identifiers) {
                $qb->setParameter($key, $identifiers);
            }
        }

        return $qb;
    }

    /**
     * Group the security identities definition.
     *
     * @param SecurityIdentityInterface[] $sids The security identities
     */
    private function groupSecurityIdentities(array $sids): array
    {
        $groupSids = [];

        if (null === $this->sharingManager) {
            throw new InvalidArgumentException('The "setSharingManager()" must be called before');
        }

        foreach ($sids as $sid) {
            if (IdentityUtils::isValid($sid)) {
                $type = $this->sharingManager->getIdentityConfig($sid->getType())->getType();
                $groupSids[$type][] = $sid->getIdentifier();
            }
        }

        return $groupSids;
    }

    /**
     * Get the security identities.
     *
     * @param null|SecurityIdentityInterface[] $sids The security identities to filter the sharing entries
     *
     * @return SecurityIdentityInterface[]
     */
    private function getSecurityIdentities(?array $sids = null): array
    {
        if (null === $sids) {
            $sids = $this->sidManager->getSecurityIdentities($this->tokenStorage->getToken());
        }

        return $sids ?? [];
    }

    /**
     * Get the role repository.
     */
    private function getRoleRepository(): EntityRepository
    {
        if (null === $this->roleRepo) {
            $this->roleRepo = $this->getRepository(RoleInterface::class);
        }

        return $this->roleRepo;
    }

    /**
     * Get the sharing repository.
     */
    private function getSharingRepository(): EntityRepository
    {
        if (null === $this->sharingRepo) {
            $this->sharingRepo = $this->getRepository(SharingInterface::class);
        }

        return $this->sharingRepo;
    }

    /**
     * Get the repository.
     *
     * @param string $classname The class name
     */
    private function getRepository($classname): EntityRepository
    {
        return ManagerUtils::getRequiredManager($this->doctrine, $classname)->getRepository($classname);
    }
}
