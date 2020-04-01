<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Annotation;

use Klipper\Component\Config\Annotation\AbstractAnnotation;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @Annotation
 * @Target({"CLASS"})
 */
class Permission extends AbstractAnnotation
{
    /**
     * @var string[]
     */
    protected $operations = [];

    /**
     * @var string[]
     */
    protected $mappingPermissions = [];

    /**
     * @var PermissionField[]
     */
    protected $fields = [];

    /**
     * @var null|string
     */
    protected $master;

    /**
     * @var array
     */
    protected $masterFieldMappingPermissions = [];

    /**
     * @var null|bool
     */
    protected $buildFields;

    /**
     * @var null|bool
     */
    protected $buildDefaultFields;

    /**
     * @return string[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * @param string[] $operations
     */
    public function setOperations(array $operations): void
    {
        $this->operations = $operations;
    }

    /**
     * @return string[]
     */
    public function getMappingPermissions(): array
    {
        return $this->mappingPermissions;
    }

    /**
     * @param string[] $mappingPermissions
     */
    public function setMappingPermissions(array $mappingPermissions): void
    {
        $this->mappingPermissions = $mappingPermissions;
    }

    /**
     * @return PermissionField[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param PermissionField[] $fields
     */
    public function setFields(array $fields): void
    {
        foreach ($fields as $field => $config) {
            $this->addField($field, $config);
        }
    }

    public function getMaster(): ?string
    {
        return $this->master;
    }

    public function setMaster(?string $master): void
    {
        $this->master = $master;
    }

    public function getMasterFieldMappingPermissions(): array
    {
        return $this->masterFieldMappingPermissions;
    }

    public function setMasterFieldMappingPermissions(array $masterFieldMappingPermissions): void
    {
        $this->masterFieldMappingPermissions = $masterFieldMappingPermissions;
    }

    public function getBuildFields(): ?bool
    {
        return $this->buildFields;
    }

    public function setBuildFields(?bool $buildFields): void
    {
        $this->buildFields = $buildFields;
    }

    public function getBuildDefaultFields(): ?bool
    {
        return $this->buildDefaultFields;
    }

    public function setBuildDefaultFields(?bool $buildDefaultFields): void
    {
        $this->buildDefaultFields = $buildDefaultFields;
    }

    /**
     * @param string          $name  The field name
     * @param PermissionField $field The permission field
     */
    protected function addField(string $name, PermissionField $field): void
    {
        $this->fields[$name] = $field;
    }
}
