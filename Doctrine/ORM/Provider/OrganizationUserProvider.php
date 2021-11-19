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
use Doctrine\Persistence\ManagerRegistry;
use Klipper\Component\DoctrineExtensions\Util\SqlFilterUtil;
use Klipper\Component\DoctrineExtra\Util\ManagerUtils;
use Klipper\Component\DoctrineExtra\Util\RepositoryUtils;
use Klipper\Component\Security\Exception\OrganizationUserNotFoundException;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\Security\Organizational\OrganizationUserProviderInterface;

/**
 * The Doctrine Orm Organization User Provider.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationUserProvider implements OrganizationUserProviderInterface
{
    protected ManagerRegistry $doctrine;

    protected ?EntityRepository $orgUserRepo = null;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function loadOrganizationUserByUser(OrganizationInterface $organization, UserInterface $user): OrganizationUserInterface
    {
        $om = ManagerUtils::getRequiredManager($this->doctrine, OrganizationUserInterface::class);
        $filters = SqlFilterUtil::disableFilters($om, [], true);
        $orgUser = $this->getOrganizationUserRepository()->createQueryBuilder('ou')
            ->addSelect('o')
            ->addSelect('u')
            ->join('ou.organization', 'o')
            ->join('ou.user', 'u')
            ->where('ou.organization = :org')
            ->andWhere('ou.user = :user')
            ->setParameter('org', $organization)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        SqlFilterUtil::enableFilters($om, $filters);

        if (null === $orgUser) {
            throw new OrganizationUserNotFoundException(sprintf(
                'User "%s" for Organization "%s" not found.',
                $user->getUserIdentifier(),
                $organization->getName()
            ));
        }

        return $orgUser;
    }

    private function getOrganizationUserRepository(): EntityRepository
    {
        if (null === $this->orgUserRepo) {
            /** @var EntityRepository $repo */
            $repo = RepositoryUtils::getRepository($this->doctrine, OrganizationUserInterface::class);
            $this->orgUserRepo = $repo;
        }

        return $this->orgUserRepo;
    }
}
