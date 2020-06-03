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
use Doctrine\Persistence\ObjectManager;
use Klipper\Component\DoctrineExtra\Util\ManagerUtils;
use Klipper\Component\DoctrineExtra\Util\RepositoryUtils;
use Klipper\Component\Security\Exception\InvalidArgumentException;
use Klipper\Component\Security\Identity\SubjectIdentityInterface;
use Klipper\Component\Security\Model\PermissionInterface;
use Klipper\Component\Security\Permission\PermissionConfigInterface;
use Klipper\Component\Security\Permission\PermissionProviderInterface;
use Klipper\Component\Security\Permission\PermissionUtils;

/**
 * The Doctrine Orm Permission Provider.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PermissionProvider implements PermissionProviderInterface
{
    protected ?EntityRepository $permissionRepo = null;

    protected ManagerRegistry $doctrine;

    /**
     * @param ManagerRegistry $doctrine The doctrine registry
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getPermissions(array $roles): array
    {
        if (empty($roles)) {
            return [];
        }

        $qb = $this->getPermissionRepository()->createQueryBuilder('p')
            ->leftJoin('p.roles', 'r')
            ->where('UPPER(r.name) IN (:roles)')
            ->setParameter('roles', $roles)
            ->orderBy('p.class', 'asc')
            ->addOrderBy('p.field', 'asc')
            ->addOrderBy('p.operation', 'asc')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param null|mixed $subject
     * @param null|mixed $contexts
     */
    public function getPermissionsBySubject($subject = null, $contexts = null): array
    {
        /** @var null|SubjectIdentityInterface $subject */
        list($subject, $field) = PermissionUtils::getSubjectAndField($subject, true);

        $qb = $this->getPermissionRepository()->createQueryBuilder('p')
            ->orderBy('p.class', 'asc')
            ->addOrderBy('p.field', 'asc')
            ->addOrderBy('p.operation', 'asc')
        ;

        $this->addWhereContexts($qb, $contexts);
        $this->addWhereOptionalField($qb, 'class', null !== $subject ? $subject->getType() : null);
        $this->addWhereOptionalField($qb, 'field', $field);

        return $qb->getQuery()->getResult();
    }

    public function getConfigPermissions($contexts = null): array
    {
        $qb = $this->getPermissionRepository()->createQueryBuilder('p')
            ->orderBy('p.class', 'asc')
            ->addOrderBy('p.field', 'asc')
            ->addOrderBy('p.operation', 'asc')
        ;

        $this->addWhereContexts($qb, $contexts);
        $this->addWhereOptionalField($qb, 'class', PermissionProviderInterface::CONFIG_CLASS);

        return $qb->getQuery()->getResult();
    }

    public function getMasterClass(PermissionConfigInterface $config): ?string
    {
        $type = $config->getType();
        $om = ManagerUtils::getManager($this->doctrine, $type);
        $this->validateMaster($config, $om);
        $masterClass = $type;

        if ($om instanceof ObjectManager) {
            foreach (explode('.', $config->getMaster()) as $master) {
                $meta = $om->getClassMetadata($masterClass);
                $masterClass = $meta->getAssociationTargetClass($master);
            }
        }

        return $masterClass;
    }

    /**
     * Validate the master config.
     *
     * @param PermissionConfigInterface $config The permission config
     * @param null|ObjectManager        $om     The doctrine object manager
     */
    private function validateMaster(PermissionConfigInterface $config, ?ObjectManager $om): void
    {
        if (null === $om) {
            $msg = 'The doctrine object manager is not found for the class "%s"';

            throw new InvalidArgumentException(sprintf($msg, $config->getType()));
        }

        if (null === $config->getMaster()) {
            $msg = 'The permission master association is not configured for the class "%s"';

            throw new InvalidArgumentException(sprintf($msg, $config->getType()));
        }
    }

    /**
     * Add the optional field condition.
     *
     * @param QueryBuilder $qb    The query builder
     * @param string       $field The field name
     * @param null|mixed   $value The value
     */
    private function addWhereOptionalField(QueryBuilder $qb, string $field, $value): void
    {
        if (null === $value) {
            $qb->andWhere('p.'.$field.' IS NULL');
        } else {
            $qb->andWhere('p.'.$field.' = :'.$field)->setParameter($field, $value);
        }
    }

    /**
     * Add the permission contexts condition.
     *
     * @param QueryBuilder         $qb       The query builder
     * @param null|string|string[] $contexts The contexts
     */
    private function addWhereContexts(QueryBuilder $qb, $contexts = null): void
    {
        if (null !== $contexts) {
            $contexts = (array) $contexts;
            $where = 'p.contexts IS NULL';

            foreach ($contexts as $context) {
                $key = 'context_'.$context;
                $where .= sprintf(' OR p.contexts LIKE :%s', $key);
                $qb->setParameter($key, '%"'.$context.'"%');
            }

            $qb->andWhere($where);
        }
    }

    /**
     * Get the permission repository.
     */
    private function getPermissionRepository(): EntityRepository
    {
        if (null === $this->permissionRepo) {
            /** @var EntityRepository $repo */
            $repo = RepositoryUtils::getRepository($this->doctrine, PermissionInterface::class);
            $this->permissionRepo = $repo;
        }

        return $this->permissionRepo;
    }
}
