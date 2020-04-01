<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Sharing\Loader;

use Klipper\Component\Config\ConfigCollectionInterface;
use Klipper\Component\Config\Loader\AbstractArrayResourceLoader;
use Klipper\Component\Security\Sharing\SharingIdentityConfigCollection;

/**
 * Sharing identity array resource loader.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class IdentityArrayResourceLoader extends AbstractArrayResourceLoader
{
    /**
     * {@inheritdoc}
     *
     * @return ConfigCollectionInterface|SharingIdentityConfigCollection
     */
    public function load($resource, $type = null): SharingIdentityConfigCollection
    {
        return parent::load($resource, $type);
    }

    /**
     * {@inheritdoc}
     */
    protected function createConfigCollection(): ConfigCollectionInterface
    {
        return new SharingIdentityConfigCollection();
    }
}
