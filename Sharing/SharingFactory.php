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

use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Sharing factory.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingFactory implements SharingFactoryInterface
{
    protected LoaderInterface $subjectLoader;

    protected LoaderInterface $identityLoader;

    /**
     * @var mixed
     */
    protected $resource;

    /**
     * @param LoaderInterface $subjectLoader  The sharing subject loader
     * @param LoaderInterface $identityLoader The sharing identity loader
     * @param mixed           $resource       The main resource to load
     */
    public function __construct(LoaderInterface $subjectLoader, LoaderInterface $identityLoader, $resource)
    {
        $this->subjectLoader = $subjectLoader;
        $this->identityLoader = $identityLoader;
        $this->resource = $resource;
    }

    public function createSubjectConfigurations(): SharingSubjectConfigCollection
    {
        return $this->subjectLoader->load($this->resource);
    }

    public function createIdentityConfigurations(): SharingIdentityConfigCollection
    {
        return $this->identityLoader->load($this->resource);
    }
}
