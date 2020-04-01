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

use Klipper\Component\Security\Exception\InvalidArgumentException;

/**
 * Permission field config.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PermissionFieldConfig implements PermissionFieldConfigInterface
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var string[]
     */
    protected $operations;

    /**
     * @var string[]
     */
    protected $mappingPermissions;

    /**
     * @var null|bool
     */
    protected $editable;

    /**
     * Constructor.
     *
     * @param string    $field              The field name
     * @param string[]  $operations         The permission operations of this field
     * @param string[]  $mappingPermissions The map of alias permission and real permission
     * @param null|bool $editable           Check if the permission is editable
     */
    public function __construct(
        string $field,
        array $operations = [],
        array $mappingPermissions = [],
        ?bool $editable = null
    ) {
        $this->field = $field;
        $this->operations = array_values($operations);
        $this->mappingPermissions = $mappingPermissions;
        $this->editable = $editable;
    }

    /**
     * {@inheritdoc}
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOperation(string $operation): bool
    {
        return \in_array($this->getMappingPermission($operation), $this->operations, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * {@inheritdoc}
     */
    public function isEditable(): bool
    {
        return null !== $this->editable ? (bool) $this->editable : empty($this->getOperations());
    }

    /**
     * {@inheritdoc}
     */
    public function getEditable(): ?bool
    {
        return $this->editable;
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingPermission(string $aliasPermission): string
    {
        return $this->mappingPermissions[$aliasPermission] ?? $aliasPermission;
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingPermissions(): array
    {
        return $this->mappingPermissions;
    }

    public function merge(PermissionFieldConfigInterface $newConfig): void
    {
        if ($this->getField() !== $newConfig->getField()) {
            throw new InvalidArgumentException(sprintf(
                'The permission field config of "%s" can be merged only with the same field, given: "%s"',
                $this->getField(),
                $newConfig->getField()
            ));
        }

        $this->operations = array_unique(array_merge($this->operations, $newConfig->getOperations()));
        $this->mappingPermissions = array_merge(
            $this->mappingPermissions,
            $newConfig->getMappingPermissions()
        );

        if (null !== $newConfig->getEditable()) {
            $this->editable = $newConfig->getEditable();
        }
    }
}
