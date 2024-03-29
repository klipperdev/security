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

use Klipper\Component\Security\Sharing\SharingSubjectConfigCollection;
use Klipper\Component\Security\Sharing\SharingSubjectConfigInterface;
use Symfony\Component\Config\Loader\Loader;

/**
 * Sharing subject configuration loader.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SubjectConfigurationLoader extends Loader
{
    protected SharingSubjectConfigCollection $configs;

    /**
     * @param SharingSubjectConfigInterface[] $configs The sharing subject configs
     */
    public function __construct(array $configs = [])
    {
        $this->configs = new SharingSubjectConfigCollection();

        foreach ($configs as $config) {
            $this->configs->add($config);
        }
    }

    public function load($resource, string $type = null): SharingSubjectConfigCollection
    {
        return $this->configs;
    }

    public function supports($resource, string $type = null): bool
    {
        return 'config' === $type;
    }
}
