<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Model\Traits;

use Klipper\Component\Security\Model\OrganizationInterface;

/**
 * Interface to indicate that the model is linked with an optional organization.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface OrganizationalOptionalInterface extends OrganizationalInterface
{
    /**
     * Set the organization.
     *
     * @param null|OrganizationInterface $organization The organization
     *
     * @return static
     */
    public function setOrganization(?OrganizationInterface $organization);
}
