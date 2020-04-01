<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Permission;

use Klipper\Component\Security\Exception\InvalidArgumentException;
use Klipper\Component\Security\Permission\PermissionFieldConfig;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class PermissionFieldConfigTest extends TestCase
{
    public function testPermissionFieldConfigByDefault(): void
    {
        $config = new PermissionFieldConfig('foo');

        static::assertSame('foo', $config->getField());
        static::assertSame([], $config->getOperations());
        static::assertFalse($config->hasOperation('foo'));
        static::assertTrue($config->isEditable());
    }

    public function testPermissionFieldConfig(): void
    {
        $operations = ['read', 'edit'];
        $alias = [
            'test' => 'read',
        ];
        $config = new PermissionFieldConfig('foo', $operations, $alias);

        static::assertSame('foo', $config->getField());
        static::assertSame($operations, $config->getOperations());
        static::assertTrue($config->hasOperation('read'));
        static::assertFalse($config->hasOperation('foo'));
        static::assertSame($alias, $config->getMappingPermissions());
        static::assertTrue($config->hasOperation('test'));
        static::assertFalse($config->isEditable());
    }

    public function testMerge(): void
    {
        $config = new PermissionFieldConfig('foo', ['read'], ['update' => 'edit'], false);

        static::assertSame('foo', $config->getField());
        static::assertSame(['read'], $config->getOperations());
        static::assertSame(['update' => 'edit'], $config->getMappingPermissions());
        static::assertFalse($config->isEditable());

        $config->merge(new PermissionFieldConfig('foo', ['update'], ['view' => 'read'], true));

        static::assertSame('foo', $config->getField());
        static::assertSame(['read', 'update'], $config->getOperations());
        static::assertSame(['update' => 'edit', 'view' => 'read'], $config->getMappingPermissions());
        static::assertTrue($config->isEditable());
    }

    public function testMergeWithInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The permission field config of "foo" can be merged only with the same field, given: "bar"');

        $config = new PermissionFieldConfig('foo');

        $config->merge(new PermissionFieldConfig('bar'));
    }
}
