<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Annotation;

use Klipper\Component\Security\Annotation\SharingSubject;
use Klipper\Component\Security\SharingVisibilities;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SharingSubjectTest extends TestCase
{
    public function testConstructor(): void
    {
        $config = new SharingSubject([
            'visibility' => SharingVisibilities::TYPE_PUBLIC,
        ]);

        static::assertSame(SharingVisibilities::TYPE_PUBLIC, $config->getVisibility());
    }
}
