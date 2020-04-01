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

use Klipper\Component\Config\AbstractConfigCollection;
use Klipper\Component\Config\ConfigCollectionInterface;

/**
 * Permission config collection.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PermissionConfigCollection extends AbstractConfigCollection
{
    /**
     * Adds a permission config.
     *
     * @param PermissionConfigInterface $config A permission config instance
     */
    public function add(PermissionConfigInterface $config): void
    {
        if (isset($this->configs[$config->getType()])) {
            $this->configs[$config->getType()]->merge($config);
        } else {
            $this->configs[$config->getType()] = $config;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return PermissionConfigInterface[]
     */
    public function all(): array
    {
        return parent::all();
    }

    /**
     * Gets a permission config by type.
     *
     * @param string $type The permission config type
     *
     * @return null|PermissionConfigInterface A PermissionConfig instance or null when not found
     */
    public function get(string $type): ?PermissionConfigInterface
    {
        return $this->configs[$type] ?? null;
    }

    /**
     * Removes a permission config or an array of permission configs by type from the collection.
     *
     * @param string|string[] $type The permission config type or an array of permission config types
     */
    public function remove(string $type): void
    {
        foreach ((array) $type as $n) {
            unset($this->configs[$n]);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param ConfigCollectionInterface|PermissionConfigCollection $collection The permission collection
     */
    public function addCollection(ConfigCollectionInterface $collection): void
    {
        foreach ($collection->all() as $config) {
            $this->add($config);
        }

        parent::addCollection($collection);
    }
}
