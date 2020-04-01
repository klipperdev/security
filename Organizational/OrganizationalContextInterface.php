<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Organizational;

use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\OrganizationUserInterface;

/**
 * Organizational Context interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface OrganizationalContextInterface
{
    /**
     * Set the current used organization.
     *
     * @param null|false|OrganizationInterface $organization The current organization
     */
    public function setCurrentOrganization($organization): void;

    /**
     * Get the current used organization.
     *
     * @return null|OrganizationInterface
     */
    public function getCurrentOrganization(): ?OrganizationInterface;

    /**
     * Set the current used organization user.
     *
     * @param null|OrganizationUserInterface $organizationUser The current organization user
     */
    public function setCurrentOrganizationUser(?OrganizationUserInterface $organizationUser): void;

    /**
     * Get the current used organization user.
     *
     * @return null|OrganizationUserInterface
     */
    public function getCurrentOrganizationUser(): ?OrganizationUserInterface;

    /**
     * Check if the current organization is not a user organization.
     *
     * @return bool
     */
    public function isOrganization(): bool;

    /**
     * Set the organizational optional filter type defined in OrganizationalTypes::OPTIONAL_FILTER_*.
     *
     * @param string $type The organizational filter type
     */
    public function setOptionalFilterType(string $type): void;

    /**
     * Get the organizational optional filter type defined in OrganizationalTypes::OPTIONAL_FILTER_*.
     *
     * @return string
     */
    public function getOptionalFilterType(): string;

    /**
     * Check if the current filter type defined in OrganizationalTypes::OPTIONAL_FILTER_* is the same.
     *
     * @param string $type The organizational filter type
     *
     * @return bool
     */
    public function isOptionalFilterType(string $type): bool;
}
