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

/**
 * Trait to indicate that the model is linked with an optional organization.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait OrganizationalOptionalTrait
{
    use OrganizationalTrait;
}
