<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Validator\Constraints;

use Klipper\Component\Security\Validator\Constraints\Sharing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SharingTest extends TestCase
{
    public function testGetTargets(): void
    {
        $constraint = new Sharing();

        static::assertEquals(Constraint::CLASS_CONSTRAINT, $constraint->getTargets());
    }
}
