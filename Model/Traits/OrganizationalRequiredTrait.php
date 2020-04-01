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
 * Trait to indicate that the model is linked with a required organization.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait OrganizationalRequiredTrait
{
    use OrganizationalTrait;

    /**
     * {@inheritdoc}
     */
    public function setOrganization(OrganizationInterface $organization): self
    {
        $this->organization = $organization;

        return $this;
    }
}
