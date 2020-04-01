<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Sharing;

use Klipper\Component\Security\Exception\InvalidArgumentException;
use Klipper\Component\Security\Sharing\SharingSubjectConfig;
use Klipper\Component\Security\SharingVisibilities;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SharingSubjectConfigTest extends TestCase
{
    public function testSharingSubjectConfigByDefault(): void
    {
        $config = new SharingSubjectConfig(MockObject::class);

        static::assertSame(MockObject::class, $config->getType());
        static::assertSame(SharingVisibilities::TYPE_NONE, $config->getVisibility());
    }

    public function testSharingSubjectConfig(): void
    {
        $config = new SharingSubjectConfig(MockObject::class, SharingVisibilities::TYPE_PRIVATE);

        static::assertSame(MockObject::class, $config->getType());
        static::assertSame(SharingVisibilities::TYPE_PRIVATE, $config->getVisibility());
    }

    public function testMerge(): void
    {
        $config = new SharingSubjectConfig(MockObject::class);

        static::assertSame(SharingVisibilities::TYPE_NONE, $config->getVisibility());

        $config->merge(new SharingSubjectConfig(MockObject::class, SharingVisibilities::TYPE_PUBLIC));

        static::assertSame(SharingVisibilities::TYPE_PUBLIC, $config->getVisibility());
    }

    public function testMergeWithInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The sharing subject config of "Klipper\Component\Security\Tests\Fixtures\Model\MockObject" can be merged only with the same type, given: "stdClass"');

        $config = new SharingSubjectConfig(MockObject::class);

        $config->merge(new SharingSubjectConfig(\stdClass::class));
    }
}
