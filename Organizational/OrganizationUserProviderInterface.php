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

use Klipper\Component\Security\Exception\OrganizationUserNotFoundException;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\Model\UserInterface;

/**
 * Organizational Provider interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface OrganizationUserProviderInterface
{
    /**
     * @throws OrganizationUserNotFoundException
     */
    public function loadOrganizationUserByUser(OrganizationInterface $organization, UserInterface $user): OrganizationUserInterface;
}
