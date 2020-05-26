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
 * Trait of add dependency entity with an optional user.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait OwnerableOptionalTrait
{
    use OwnerableTrait;
}
