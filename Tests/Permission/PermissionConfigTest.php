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
use Klipper\Component\Security\Permission\PermissionConfig;
use Klipper\Component\Security\Permission\PermissionFieldConfig;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class PermissionConfigTest extends TestCase
{
    public function testPermissionConfigByDefault(): void
    {
        $operations = ['create', 'view', 'update', 'delete'];
        $config = new PermissionConfig(MockObject::class, $operations);

        static::assertSame(MockObject::class, $config->getType());
        static::assertSame([], $config->getFields());
        static::assertNull($config->getMaster());
    }

    public function testPermissionConfig(): void
    {
        $operations = ['invite', 'view', 'update', 'revoke'];
        $alias = [
            'create' => 'invite',
            'delete' => 'revoke',
        ];
        $fields = [
            'name' => new PermissionFieldConfig('name'),
        ];
        $master = 'foo';
        $masterMapping = [
            'view' => 'read',
        ];
        $config = new PermissionConfig(
            MockObject::class,
            $operations,
            $alias,
            array_values($fields),
            $master,
            $masterMapping,
            false,
            false
        );

        static::assertSame(MockObject::class, $config->getType());

        static::assertSame($fields, $config->getFields());
        static::assertSame($fields['name'], $config->getField('name'));
        static::assertNull($config->getField('foo'));

        static::assertSame($master, $config->getMaster());
        static::assertSame($masterMapping, $config->getMasterFieldMappingPermissions());
        static::assertFalse($config->buildFields());
        static::assertFalse($config->buildDefaultFields());

        static::assertSame($operations, $config->getOperations());
        static::assertTrue($config->hasOperation('view'));
        static::assertFalse($config->hasOperation('foo'));
        static::assertSame($alias, $config->getMappingPermissions());
        static::assertTrue($config->hasOperation('create'));
    }

    public function testMerge(): void
    {
        $nameField = new PermissionFieldConfig('name');
        $idField = new PermissionFieldConfig('id');

        $config = new PermissionConfig(
            MockObject::class,
            ['invite', 'view', 'update', 'revoke'],
            [
                'create' => 'invite',
                'delete' => 'revoke',
            ],
            [
                $nameField,
            ],
            'foo',
            [
                'view' => 'read',
            ]
        );

        static::assertSame(MockObject::class, $config->getType());
        static::assertSame(['name' => $nameField], $config->getFields());
        static::assertSame(['invite', 'view', 'update', 'revoke'], $config->getOperations());
        static::assertSame(['create' => 'invite', 'delete' => 'revoke'], $config->getMappingPermissions());
        static::assertSame('foo', $config->getMaster());
        static::assertSame(['view' => 'read'], $config->getMasterFieldMappingPermissions());
        static::assertTrue($config->buildFields());
        static::assertTrue($config->buildDefaultFields());

        $config->merge(new PermissionConfig(
            MockObject::class,
            ['delete'],
            [
                'view' => 'read',
            ],
            [
                'id' => $idField, 'name' => new PermissionFieldConfig('name'),
            ],
            'foo',
            [
                'create' => 'edit',
            ],
            false,
            false
        ));

        static::assertSame(MockObject::class, $config->getType());
        static::assertSame(['id' => $idField, 'name' => $nameField], $config->getFields());
        static::assertSame(['invite', 'view', 'update', 'revoke', 'delete'], $config->getOperations());
        static::assertSame(['create' => 'invite', 'delete' => 'revoke', 'view' => 'read'], $config->getMappingPermissions());
        static::assertSame('foo', $config->getMaster());
        static::assertSame(['view' => 'read', 'create' => 'edit'], $config->getMasterFieldMappingPermissions());
        static::assertFalse($config->buildFields());
        static::assertFalse($config->buildDefaultFields());
    }

    public function testMergeWithInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The permission config of "Klipper\Component\Security\Tests\Fixtures\Model\MockObject" can be merged only with the same type, given: "stdClass"');

        $config = new PermissionConfig(MockObject::class);

        $config->merge(new PermissionConfig(\stdClass::class));
    }
}
