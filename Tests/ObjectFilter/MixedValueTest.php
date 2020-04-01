<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\ObjectFilter;

use Klipper\Component\Security\ObjectFilter\MixedValue;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class MixedValueTest extends TestCase
{
    public function getValues(): array
    {
        return [
            ['string', null],
            [42, null],
            [42.5, null],
            [true, null],
            [false, null],
            [null, null],
            [new \stdClass(), null],
            [['42'], []],
        ];
    }

    /**
     * @dataProvider getValues
     *
     * @param mixed $value    The value
     * @param mixed $expected The expected value
     */
    public function test($value, $expected): void
    {
        $mv = new MixedValue();

        static::assertTrue($mv->supports($value));
        static::assertSame($expected, $mv->getValue($value));
    }
}
