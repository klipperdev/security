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

use Klipper\Component\Security\Model\Traits\OrganizationalOptionalInterface;
use Klipper\Component\Security\Model\Traits\OrganizationalOptionalTrait;
use Klipper\Component\Security\Model\Traits\UserOrganizationUsersInterface;
use Klipper\Component\Security\Model\Traits\UserOrganizationUsersTrait;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MockUserOrganizationUsers extends MockUserRoleable implements OrganizationalOptionalInterface, UserOrganizationUsersInterface
{
    use UserOrganizationUsersTrait;
    use OrganizationalOptionalTrait;
}
