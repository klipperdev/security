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

use Klipper\Component\Security\Model\Traits\OrganizationalRequiredInterface;
use Klipper\Component\Security\Model\Traits\OrganizationalRequiredTrait;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MockOrgRequiredRole extends MockRole implements OrganizationalRequiredInterface
{
    use OrganizationalRequiredTrait;
}
