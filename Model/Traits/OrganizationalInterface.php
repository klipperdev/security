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
 * Interface to indicate that the model is linked with an organization.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface OrganizationalInterface
{
    /**
     * Set the organization.
     *
     * @param null|OrganizationInterface $organization The organization
     *
     * @return static
     */
    public function setOrganization(?OrganizationInterface $organization);

    /**
     * Get the organization.
     */
    public function getOrganization(): ?OrganizationInterface;

    /**
     * Get the organization id.
     *
     * @return null|int|string
     */
    public function getOrganizationId();
}
