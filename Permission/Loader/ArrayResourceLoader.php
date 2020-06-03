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

use Klipper\Component\Config\ConfigCollectionInterface;
use Klipper\Component\Config\Loader\AbstractArrayResourceLoader;
use Klipper\Component\Security\Permission\PermissionConfigCollection;

/**
 * Permission array resource loader.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ArrayResourceLoader extends AbstractArrayResourceLoader
{
    /**
     * @param mixed $resource
     *
     * @return ConfigCollectionInterface|PermissionConfigCollection
     */
    public function load($resource, string $type = null): PermissionConfigCollection
    {
        return parent::load($resource, $type);
    }

    protected function createConfigCollection(): ConfigCollectionInterface
    {
        return new PermissionConfigCollection();
    }
}
