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

use Klipper\Component\Security\Annotation\PermissionField;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class PermissionFieldTest extends TestCase
{
    public function testConstructor(): void
    {
        $config = new PermissionField([
            'operations' => ['read'],
            'mappingPermissions' => ['update' => 'edit'],
            'editable' => true,
        ]);

        static::assertSame(['read'], $config->getOperations());
        static::assertSame(['update' => 'edit'], $config->getMappingPermissions());
        static::assertTrue($config->getEditable());
    }
}
