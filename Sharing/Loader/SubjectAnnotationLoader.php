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

use Klipper\Component\Config\Loader\AbstractAnnotationLoader;
use Klipper\Component\Security\Annotation\SharingSubject;
use Klipper\Component\Security\Sharing\SharingSubjectConfig;
use Klipper\Component\Security\Sharing\SharingSubjectConfigCollection;
use Symfony\Component\Config\Resource\DirectoryResource;

/**
 * Sharing subject annotation loader.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SubjectAnnotationLoader extends AbstractAnnotationLoader
{
    public function supports($resource, string $type = null): bool
    {
        return 'annotation' === $type && \is_string($resource) && is_dir($resource);
    }

    public function load($resource, string $type = null): SharingSubjectConfigCollection
    {
        $configs = new SharingSubjectConfigCollection();
        $configs->addResource(new DirectoryResource($resource));

        foreach ($this->classFinder->findClasses([$resource]) as $class) {
            try {
                $refClass = new \ReflectionClass($class);
                $classAnnotations = $this->reader->getClassAnnotations($refClass);

                foreach ($classAnnotations as $annotation) {
                    if ($annotation instanceof SharingSubject) {
                        $configs->add(new SharingSubjectConfig(
                            $class,
                            $annotation->getVisibility()
                        ));
                    }
                }
            } catch (\ReflectionException $e) {
                // skip
            }
        }

        return $configs;
    }
}
