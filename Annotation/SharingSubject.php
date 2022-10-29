<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Annotation;

use Klipper\Component\Config\Annotation\AbstractAnnotation;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @Annotation
 *
 * @Target({"CLASS"})
 */
class SharingSubject extends AbstractAnnotation
{
    /**
     * @see \Klipper\Component\Security\SharingVisibilities
     */
    protected ?string $visibility = null;

    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    public function setVisibility(?string $visibility): void
    {
        $this->visibility = $visibility;
    }
}
