<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Fixtures\Model;

use Klipper\Component\Security\Model\Traits\OwnerableInterface;
use Klipper\Component\Security\Model\Traits\OwnerableTrait;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MockObjectOwnerable extends MockObject implements OwnerableInterface
{
    use OwnerableTrait;
}
