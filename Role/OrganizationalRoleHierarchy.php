<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Role;

use Doctrine\Persistence\ManagerRegistry as ManagerRegistryInterface;
use Klipper\Component\Security\Model\RoleInterface;
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Klipper\Component\Security\Organizational\OrganizationalUtil;
use Psr\Cache\CacheItemPoolInterface;

/**
 * RoleHierarchy defines a role hierarchy.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationalRoleHierarchy extends RoleHierarchy
{
    /**
     * @var null|OrganizationalContextInterface
     */
    protected $context;

    /**
     * Constructor.
     *
     * @param array                               $hierarchy     An array defining the hierarchy
     * @param ManagerRegistryInterface            $registry      The doctrine registry
     * @param null|CacheItemPoolInterface         $cache         The cache
     * @param null|OrganizationalContextInterface $context       The organizational context
     * @param string                              $roleClassname The classname of role
     */
    public function __construct(
        array $hierarchy,
        ManagerRegistryInterface $registry,
        ?CacheItemPoolInterface $cache = null,
        ?OrganizationalContextInterface $context = null,
        string $roleClassname = RoleInterface::class
    ) {
        parent::__construct($hierarchy, $registry, $cache, $roleClassname);
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUniqueId(array $roleNames): string
    {
        $id = parent::getUniqueId($roleNames);

        if (null !== $this->context && null !== ($org = $this->context->getCurrentOrganization())) {
            $id = ($org->isUserOrganization() ? 'user' : $org->getId()).'__'.$id;
        }

        return $id;
    }

    /**
     * {@inheritdoc}
     */
    protected function formatRoles(array $roles): array
    {
        return array_map(static function ($role) {
            return OrganizationalUtil::format($role);
        }, $roles);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildRoleSuffix(?string $role): string
    {
        return null !== $role ? OrganizationalUtil::getSuffix($role) : '';
    }

    /**
     * {@inheritdoc}
     */
    protected function cleanRoleNames(array $roles): array
    {
        return array_map(static function ($role) {
            return OrganizationalUtil::format($role);
        }, $roles);
    }

    /**
     * {@inheritdoc}
     */
    protected function formatCleanedRoleName(string $name): string
    {
        return OrganizationalUtil::format($name);
    }
}
