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
use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * Permission config.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PermissionConfig implements PermissionConfigInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string[]
     */
    protected $operations;

    /**
     * @var string[]
     */
    protected $mappingPermissions;

    /**
     * @var PermissionFieldConfigInterface[]
     */
    protected $fields = [];

    /**
     * @var null|PropertyPathInterface|string
     */
    protected $master;

    /**
     * @var array
     */
    protected $masterFieldMappingPermissions;

    /**
     * @var null|bool
     */
    protected $buildFields;

    /**
     * @var null|bool
     */
    protected $buildDefaultFields;

    /**
     * Constructor.
     *
     * @param string                            $type                          The type, typically, this is the PHP class name
     * @param string[]                          $operations                    The permission operations of this type
     * @param string[]                          $mappingPermissions            The map of alias permission and real permission
     * @param PermissionFieldConfigInterface[]  $fields                        The field configurations
     * @param null|PropertyPathInterface|string $master                        The property path of master
     * @param array[]                           $masterFieldMappingPermissions The map of field permission of this type with the permission of master type
     * @param null|bool                         $buildFields                   Check if the fields must be built even if no field config is added
     * @param null|bool                         $buildDefaultFields            check if the default fields must be built
     */
    public function __construct(
        $type,
        array $operations = [],
        array $mappingPermissions = [],
        array $fields = [],
        $master = null,
        array $masterFieldMappingPermissions = [],
        $buildFields = null,
        $buildDefaultFields = null
    ) {
        $this->type = $type;
        $this->operations = array_values($operations);
        $this->mappingPermissions = $mappingPermissions;
        $this->master = $master;
        $this->masterFieldMappingPermissions = $masterFieldMappingPermissions;
        $this->buildFields = $buildFields;
        $this->buildDefaultFields = $buildDefaultFields;

        foreach ($fields as $field) {
            $this->addField($field);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return $this->type;
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
    public function hasField(string $field): bool
    {
        return isset($this->fields[$field]);
    }

    /**
     * {@inheritdoc}
     */
    public function getField(string $field): ?PermissionFieldConfigInterface
    {
        return $this->fields[$field] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaster()
    {
        return $this->master;
    }

    /**
     * {@inheritdoc}
     */
    public function getMasterFieldMappingPermissions(): array
    {
        return $this->masterFieldMappingPermissions;
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

    /**
     * {@inheritdoc}
     */
    public function buildFields(): bool
    {
        return $this->buildFields ?? true;
    }

    /**
     * {@inheritdoc}
     */
    public function getBuildFields(): ?bool
    {
        return $this->buildFields;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDefaultFields(): bool
    {
        return $this->buildDefaultFields ?? true;
    }

    /**
     * {@inheritdoc}
     */
    public function getBuildDefaultFields(): ?bool
    {
        return $this->buildDefaultFields;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(PermissionConfigInterface $newConfig): void
    {
        if ($this->getType() !== $newConfig->getType()) {
            throw new InvalidArgumentException(sprintf(
                'The permission config of "%s" can be merged only with the same type, given: "%s"',
                $this->getType(),
                $newConfig->getType()
            ));
        }

        $this->operations = array_unique(array_merge($this->operations, $newConfig->getOperations()));
        $this->mappingPermissions = array_merge(
            $this->mappingPermissions,
            $newConfig->getMappingPermissions()
        );
        $this->masterFieldMappingPermissions = array_merge(
            $this->masterFieldMappingPermissions,
            $newConfig->getMasterFieldMappingPermissions()
        );

        if (null !== $newConfig->getMaster()) {
            $this->master = $newConfig->getMaster();
        }

        if (null !== $newConfig->getBuildFields()) {
            $this->buildFields = $newConfig->getBuildFields();
        }

        if (null !== $newConfig->getBuildDefaultFields()) {
            $this->buildDefaultFields = $newConfig->getBuildDefaultFields();
        }

        foreach ($newConfig->getFields() as $newFieldConfig) {
            if (isset($this->fields[$newFieldConfig->getField()])) {
                $this->fields[$newFieldConfig->getField()]->merge($newFieldConfig);
            } else {
                $this->addField($newFieldConfig);
            }
        }
    }

    /**
     * Add the permission field configuration.
     *
     * @param PermissionFieldConfigInterface $fieldConfig The permission field configuration
     *
     * @return static
     */
    private function addField(PermissionFieldConfigInterface $fieldConfig): PermissionConfig
    {
        $this->fields[$fieldConfig->getField()] = $fieldConfig;
        ksort($this->fields);

        return $this;
    }
}
