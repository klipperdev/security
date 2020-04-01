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
 * Sharing subject config collection.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingSubjectConfigCollection extends AbstractConfigCollection
{
    /**
     * Adds a sharing subject config.
     *
     * @param SharingSubjectConfigInterface $config A sharing subject config instance
     */
    public function add(SharingSubjectConfigInterface $config): void
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
     * @return SharingSubjectConfigInterface[]
     */
    public function all(): array
    {
        return parent::all();
    }

    /**
     * Gets a sharing subject config by type.
     *
     * @param string $type The sharing subject config type
     *
     * @return null|SharingSubjectConfigInterface A SharingSubjectConfig instance or null when not found
     */
    public function get(string $type): ?SharingSubjectConfigInterface
    {
        return $this->configs[$type] ?? null;
    }

    /**
     * Removes a sharing subject config or an array of sharing subject configs by type from the collection.
     *
     * @param string|string[] $type The sharing subject config type or an array of sharing subject config types
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
     * @param ConfigCollectionInterface|SharingSubjectConfigCollection $collection The collection
     */
    public function addCollection(ConfigCollectionInterface $collection): void
    {
        foreach ($collection->all() as $config) {
            $this->add($config);
        }

        parent::addCollection($collection);
    }
}
