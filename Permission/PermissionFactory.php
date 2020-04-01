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

use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Permission factory.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PermissionFactory implements PermissionFactoryInterface
{
    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * @var mixed
     */
    protected $resource;

    /**
     * @var array
     */
    protected $defaultPermissions;

    /**
     * Constructor.
     *
     * @param LoaderInterface $loader             The permission loader
     * @param mixed           $resource           The main resource to load
     * @param array           $defaultPermissions The map of the default permissions
     */
    public function __construct(LoaderInterface $loader, $resource, array $defaultPermissions = [])
    {
        $this->loader = $loader;
        $this->defaultPermissions = $defaultPermissions;
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     *
     * @throws
     */
    public function createConfigurations(): PermissionConfigCollection
    {
        /** @var PermissionConfigCollection $configs */
        $configs = $this->loader->load($this->resource);

        foreach ($configs as $config) {
            $this->configureDefaultMasterFieldMappingPermissions($config);
            $this->configureDefaultFields($config);
        }

        return $configs;
    }

    /**
     * Configure the master field mapping permissions with the default mapping.
     *
     * @param PermissionConfigInterface $config The permission config
     */
    private function configureDefaultMasterFieldMappingPermissions(PermissionConfigInterface $config): void
    {
        $defaultMapping = $this->defaultPermissions['master_mapping_permissions'] ?? [];

        if (!empty($defaultMapping)
                && empty($config->getMasterFieldMappingPermissions())
                && null !== $config->getMaster()) {
            $config->merge(new PermissionConfig($config->getType(), [], [], [], null, $defaultMapping));
        }
    }

    /**
     * Configure the master field mapping permissions with the default mapping.
     *
     * @param PermissionConfigInterface $config The permission config
     */
    private function configureDefaultFields(PermissionConfigInterface $config): void
    {
        $defaultFields = $this->defaultPermissions['fields'] ?? [];
        $hasDefaults = \count($defaultFields) > 0;
        $buildField = $config->buildFields() && empty($config->getFields());
        $buildDefaultField = $config->buildDefaultFields() && $hasDefaults;

        $newFields = $this->configureNewFields($config, $defaultFields, $buildField, $buildDefaultField);

        if (\count($newFields) > 0) {
            $config->merge(new PermissionConfig($config->getType(), [], [], $newFields));
        }
    }

    /**
     * Configure the new fields.
     *
     * @param PermissionConfigInterface $config            The permission config
     * @param array                     $defaultFields     The default fields
     * @param bool                      $buildField        Check if the field must be created
     * @param bool                      $buildDefaultField Check if the field must be created with the default permissions
     *
     * @throws
     *
     * @return PermissionFieldConfig[]
     */
    private function configureNewFields(PermissionConfigInterface $config, array $defaultFields, $buildField, $buildDefaultField): array
    {
        $ref = new \ReflectionClass($config->getType());
        $newFields = [];

        if ($buildField || $buildDefaultField) {
            foreach ($ref->getProperties() as $property) {
                $field = $property->getName();

                if ($buildDefaultField && isset($defaultFields[$field]) && !$config->hasField($field)) {
                    $newFields[] = new PermissionFieldConfig(
                        $field,
                        $defaultFields[$field]['operations'] ?? [],
                        $defaultFields[$field]['mapping_permissions'] ?? [],
                        $defaultFields[$field]['editable'] ?? null
                    );
                } elseif ($buildField && empty($config->getFields())) {
                    $newFields[] = new PermissionFieldConfig($field);
                }
            }
        }

        return $newFields;
    }
}
