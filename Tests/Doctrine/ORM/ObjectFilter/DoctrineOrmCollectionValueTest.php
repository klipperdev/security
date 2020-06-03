<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Doctrine\ORM\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Klipper\Component\Security\Doctrine\ORM\ObjectFilter\DoctrineOrmCollectionValue;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class DoctrineOrmCollectionValueTest extends TestCase
{
    public function getValues(): array
    {
        return [
            [$this->getMockBuilder(Collection::class)->getMock(), true],
            [$this->getMockBuilder(\stdClass::class)->getMock(), false],
            ['string', false],
            [42, false],
        ];
    }

    /**
     * @dataProvider getValues
     *
     * @param mixed $value  The value
     * @param bool  $result The expected result
     */
    public function testSupports($value, bool $result): void
    {
        $collectionValue = new DoctrineOrmCollectionValue();

        static::assertSame($result, $collectionValue->supports($value));
    }

    /**
     * @dataProvider getValues
     *
     * @param mixed $value The value
     */
    public function testGetValue($value): void
    {
        $collectionValue = new DoctrineOrmCollectionValue();

        $newValue = $collectionValue->getValue($value);

        static::assertNotSame($value, $newValue);
        static::assertInstanceOf(ArrayCollection::class, $newValue);
        static::assertCount(0, $newValue);
    }
}
