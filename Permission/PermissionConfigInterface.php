<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Permission;

use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * Permission config Interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface PermissionConfigInterface
{
    /**
     * Get the type. Typically, this is the PHP class name.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Check if the operation is defined.
     *
     * @param string $operation The operation name
     *
     * @return bool
     */
    public function hasOperation(string $operation): bool;

    /**
     * Get the available operations for this type.
     *
     * @return string[]
     */
    public function getOperations(): array;

    /**
     * Check if the field configuration exists.
     *
     * @param string $field The field name
     *
     * @return bool
     */
    public function hasField(string $field): bool;

    /**
     * Get the field configuration.
     *
     * @param string $field The field name
     *
     * @return null|PermissionFieldConfigInterface
     */
    public function getField(string $field): ?PermissionFieldConfigInterface;

    /**
     * Get the available fields.
     *
     * @return PermissionFieldConfigInterface[]
     */
    public function getFields(): array;

    /**
     * Get the master relation of permission.
     *
     * @return null|PropertyPathInterface|string
     */
    public function getMaster();

    /**
     * Get the map of the permission of master type with the field permission of this type.
     *
     * Example: [
     *     'view' => 'read',
     *     'create' => 'edit',
     *     'update' => 'edit',
     * ]
     *
     * @return array
     */
    public function getMasterFieldMappingPermissions(): array;

    /**
     * Get the real permission associated with the alias permission.
     *
     * Example: [
     *     'create' => 'invite',
     *     'delete' => 'revoke',
     * ]
     *
     * @param string $aliasPermission The operation or alias of operation
     *
     * @return string
     */
    public function getMappingPermission(string $aliasPermission): string;

    /**
     * Get the map of alias permission and real permission.
     *
     * @return string[]
     */
    public function getMappingPermissions(): array;

    /**
     * Check if the fields must be built even if no field config is added.
     *
     * @return bool
     */
    public function buildFields(): bool;

    /**
     * Get the value of build fields.
     *
     * @return null|bool
     */
    public function getBuildFields(): ?bool;

    /**
     * Check if the default fields must be built.
     *
     * @return bool
     */
    public function buildDefaultFields(): bool;

    /**
     * Get the value of build default fields.
     *
     * @return null|bool
     */
    public function getBuildDefaultFields(): ?bool;

    /**
     * Merge the new permission config.
     *
     * @param PermissionConfigInterface $newConfig The new permission config
     */
    public function merge(PermissionConfigInterface $newConfig): void;
}
