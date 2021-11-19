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

use Klipper\Component\Security\Exception\RuntimeException;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\OrganizationalTypes;

/**
 * Static Organizational Context.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class StaticOrganizationalContext implements OrganizationalContextInterface
{
    /**
     * @var null|false|OrganizationInterface
     */
    protected $organization;

    protected ?OrganizationUserInterface $organizationUser;

    protected string $optionalFilterType;

    /**
     * @param null|false|OrganizationInterface $organization
     */
    public function __construct(
        $organization,
        ?OrganizationUserInterface $organizationUser,
        string $optionalFilterType = OrganizationalTypes::OPTIONAL_FILTER_ALL
    ) {
        $this->organization = $organization;
        $this->organizationUser = $organizationUser;
        $this->optionalFilterType = $optionalFilterType;
    }

    /**
     * @param mixed $organization
     */
    public function setCurrentOrganization($organization): void
    {
        throw new RuntimeException('This method cannot be used in static organizational context');
    }

    public function getCurrentOrganization(): ?OrganizationInterface
    {
        return $this->organization instanceof OrganizationInterface ? $this->organization : null;
    }

    public function setCurrentOrganizationUser(?OrganizationUserInterface $organizationUser): void
    {
        throw new RuntimeException('This method cannot be used in static organizational context');
    }

    public function getCurrentOrganizationUser(): ?OrganizationUserInterface
    {
        return $this->organizationUser;
    }

    public function isOrganization(): bool
    {
        return null !== $this->getCurrentOrganization()
            && !$this->getCurrentOrganization()->isUserOrganization()
            && null !== $this->getCurrentOrganizationUser();
    }

    public function setOptionalFilterType(string $type): void
    {
        throw new RuntimeException('This method cannot be used in static organizational context');
    }

    public function getOptionalFilterType(): string
    {
        return $this->optionalFilterType;
    }

    public function isOptionalFilterType(string $type): bool
    {
        return $type === $this->getOptionalFilterType();
    }
}
