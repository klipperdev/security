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

use Klipper\Component\Security\ObjectFilter\ObjectFilterExtension;
use Klipper\Component\Security\ObjectFilter\ObjectFilterVoterInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class ObjectFilterExtensionTest extends TestCase
{
    public function testFilterValue(): void
    {
        $voter1 = $this->getMockBuilder(ObjectFilterVoterInterface::class)->getMock();
        $voter1->expects(static::once())
            ->method('supports')
            ->willReturn(false)
        ;
        $voter1->expects(static::never())
            ->method('getValue')
        ;

        $voter2 = $this->getMockBuilder(ObjectFilterVoterInterface::class)->getMock();
        $voter2->expects(static::once())
            ->method('supports')
            ->willReturn(true)
        ;
        $voter2->expects(static::once())
            ->method('getValue')
            ->willReturn('TEST')
        ;

        $voter3 = $this->getMockBuilder(ObjectFilterVoterInterface::class)->getMock();
        $voter3->expects(static::never())
            ->method('supports')
        ;
        $voter3->expects(static::never())
            ->method('getValue')
        ;

        $ofe = new ObjectFilterExtension([
            $voter1,
            $voter2,
            $voter3,
        ]);

        static::assertSame('TEST', $ofe->filterValue(42));
    }
}
