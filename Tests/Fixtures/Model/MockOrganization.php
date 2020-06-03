<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Fixtures\Model;

use Klipper\Component\Security\Model\Traits\OrganizationGroupsInterface;
use Klipper\Component\Security\Model\Traits\OrganizationGroupsTrait;
use Klipper\Component\Security\Model\Traits\OrganizationRolesInterface;
use Klipper\Component\Security\Model\Traits\OrganizationRolesTrait;
use Klipper\Component\Security\Model\Traits\OrganizationTrait;
use Klipper\Component\Security\Model\Traits\RoleableInterface;
use Klipper\Component\Security\Model\Traits\RoleableTrait;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MockOrganization implements RoleableInterface, OrganizationRolesInterface, OrganizationGroupsInterface
{
    use OrganizationTrait;
    use RoleableTrait;
    use OrganizationRolesTrait;
    use OrganizationGroupsTrait;

    protected ?int $id = null;

    /**
     * @param string $name The unique name
     * @param int    $id   The id
     */
    public function __construct(string $name, int $id = 23)
    {
        $this->name = $name;
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
