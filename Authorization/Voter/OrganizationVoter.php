<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Authorization\Voter;

use Klipper\Component\Security\Model\OrganizationInterface;

/**
 * OrganizationVoter to determine the organization granted on current user defined in token.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationVoter extends AbstractIdentityVoter
{
    protected function getValidType(): string
    {
        return OrganizationInterface::class;
    }

    protected function getDefaultPrefix(): string
    {
        return 'ORG_';
    }
}
