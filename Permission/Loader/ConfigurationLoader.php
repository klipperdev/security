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

use Klipper\Component\Security\Permission\PermissionConfigCollection;
use Klipper\Component\Security\Permission\PermissionConfigInterface;
use Symfony\Component\Config\Loader\Loader;

/**
 * Permission configuration loader.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ConfigurationLoader extends Loader
{
    protected PermissionConfigCollection $configs;

    /**
     * @param PermissionConfigInterface[] $configs The permission configs
     */
    public function __construct(array $configs = [])
    {
        $this->configs = new PermissionConfigCollection();

        foreach ($configs as $config) {
            $this->configs->add($config);
        }
    }

    public function load($resource, string $type = null): PermissionConfigCollection
    {
        return $this->configs;
    }

    public function supports($resource, string $type = null): bool
    {
        return 'config' === $type;
    }
}
