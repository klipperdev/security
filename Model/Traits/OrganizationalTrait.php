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
 * Trait to indicate that the model is linked with an organization.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait OrganizationalTrait
{
    /**
     * @var null|OrganizationInterface
     */
    protected $organization;

    /**
     * {@inheritdoc}
     */
    public function getOrganization(): ?OrganizationInterface
    {
        return $this->organization;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationId()
    {
        return null !== $this->getOrganization()
            ? $this->getOrganization()->getId()
            : null;
    }
}
