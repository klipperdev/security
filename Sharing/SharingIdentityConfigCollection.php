<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Sharing;

use Klipper\Component\Config\AbstractConfigCollection;
use Klipper\Component\Config\ConfigCollectionInterface;

/**
 * Sharing identity config collection.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingIdentityConfigCollection extends AbstractConfigCollection
{
    /**
     * Adds a sharing identity config.
     *
     * @param SharingIdentityConfigInterface $config A sharing identity config instance
     */
    public function add(SharingIdentityConfigInterface $config): void
    {
        if (isset($this->configs[$config->getType()])) {
            $this->configs[$config->getType()]->merge($config);
        } else {
            $this->configs[$config->getType()] = $config;
        }
    }

    /**
     * @return SharingIdentityConfigInterface[]
     */
    public function all(): array
    {
        return parent::all();
    }

    /**
     * Gets a sharing identity config by type.
     *
     * @param string $type The sharing identity config type
     *
     * @return null|SharingIdentityConfigInterface A SharingIdentityConfig instance or null when not found
     */
    public function get(string $type): ?SharingIdentityConfigInterface
    {
        return $this->configs[$type] ?? null;
    }

    /**
     * Removes a sharing identity config or an array of sharing identity configs by type from the collection.
     *
     * @param string|string[] $type The sharing identity config type or an array of sharing identity config types
     */
    public function remove(string $type): void
    {
        foreach ((array) $type as $n) {
            unset($this->configs[$n]);
        }
    }

    /**
     * @param ConfigCollectionInterface|SharingIdentityConfigCollection $collection The collection
     */
    public function addCollection(ConfigCollectionInterface $collection): void
    {
        foreach ($collection->all() as $config) {
            $this->add($config);
        }

        parent::addCollection($collection);
    }
}
