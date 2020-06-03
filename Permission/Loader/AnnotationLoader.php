<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Permission\Loader;

use Klipper\Component\Config\Loader\AbstractAnnotationLoader;
use Klipper\Component\Security\Annotation\Permission;
use Klipper\Component\Security\Annotation\PermissionField;
use Klipper\Component\Security\Permission\PermissionConfig;
use Klipper\Component\Security\Permission\PermissionConfigCollection;
use Klipper\Component\Security\Permission\PermissionFieldConfig;
use Klipper\Component\Security\Permission\PermissionFieldConfigInterface;
use Symfony\Component\Config\Resource\DirectoryResource;

/**
 * Permission annotation loader.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class AnnotationLoader extends AbstractAnnotationLoader
{
    public function supports($resource, string $type = null): bool
    {
        return 'annotation' === $type && \is_string($resource) && is_dir($resource);
    }

    public function load($resource, string $type = null): PermissionConfigCollection
    {
        $configs = new PermissionConfigCollection();
        $configs->addResource(new DirectoryResource($resource));

        foreach ($this->classFinder->findClasses([$resource]) as $class) {
            try {
                $refClass = new \ReflectionClass($class);
                $configs = $this->getConfigurations($refClass, $configs);

                if (!empty($fieldConfigurations = $this->getFieldConfigurations($refClass))) {
                    $configs->add(new PermissionConfig($class, [], [], $fieldConfigurations));
                }
            } catch (\ReflectionException $e) {
                // skip
            }
        }

        return $configs;
    }

    /**
     * Get the permission configurations.
     *
     * @param \ReflectionClass           $refClass The reflection class
     * @param PermissionConfigCollection $configs  The permission config collection
     */
    private function getConfigurations(\ReflectionClass $refClass, PermissionConfigCollection $configs): PermissionConfigCollection
    {
        $classAnnotations = $this->reader->getClassAnnotations($refClass);

        foreach ($classAnnotations as $annotation) {
            if ($annotation instanceof Permission) {
                $configs->add(new PermissionConfig(
                    $refClass->name,
                    $annotation->getOperations(),
                    $annotation->getMappingPermissions(),
                    $this->convertPermissionFields($annotation->getFields()),
                    $annotation->getMaster(),
                    $annotation->getMasterFieldMappingPermissions(),
                    $annotation->getBuildFields(),
                    $annotation->getBuildDefaultFields()
                ));
            }
        }

        return $configs;
    }

    /**
     * Get the permission field configuration.
     *
     * @param \ReflectionClass $refClass The reflection class
     *
     * @return PermissionFieldConfigInterface[]
     */
    private function getFieldConfigurations(\ReflectionClass $refClass): array
    {
        $configs = [];

        foreach ($refClass->getProperties() as $refProperty) {
            $propertyAnnotations = $this->reader->getPropertyAnnotations($refProperty);
            $field = $refProperty->name;

            foreach ($propertyAnnotations as $annotation) {
                if ($annotation instanceof PermissionField) {
                    $config = new PermissionFieldConfig(
                        $field,
                        $annotation->getOperations(),
                        $annotation->getMappingPermissions(),
                        $annotation->getEditable()
                    );

                    if (isset($configs[$field])) {
                        $configs[$field]->merge($config);
                    } else {
                        $configs[$field] = $config;
                    }
                }
            }
        }

        return $configs;
    }

    /**
     * Convert the permission fields.
     *
     * @param object[] $fieldAnnotations The annotations in fields of permission annotation
     *
     * @return PermissionFieldConfigInterface[]
     */
    private function convertPermissionFields(array $fieldAnnotations): array
    {
        $configs = [];

        foreach ($fieldAnnotations as $field => $annotation) {
            if ($annotation instanceof PermissionField) {
                $configs[] = $this->convertPermissionField($field, $annotation);
            }
        }

        return $configs;
    }

    /**
     * Convert the permission field.
     *
     * @param string          $field      The field name
     * @param PermissionField $annotation The permission field annotation
     */
    private function convertPermissionField(string $field, PermissionField $annotation): PermissionFieldConfigInterface
    {
        return new PermissionFieldConfig(
            $field,
            $annotation->getOperations(),
            $annotation->getMappingPermissions(),
            $annotation->getEditable()
        );
    }
}
