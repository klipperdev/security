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

use Klipper\Component\Security\Model\GroupInterface;

/**
 * Voter to determine the groups granted on current user defined in token.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class GroupVoter extends AbstractIdentityVoter
{
    protected function getValidType(): string
    {
        return GroupInterface::class;
    }

    protected function getDefaultPrefix(): string
    {
        return 'GROUP_';
    }
}
