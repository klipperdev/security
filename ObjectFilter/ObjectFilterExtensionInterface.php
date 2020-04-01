<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\ObjectFilter;

/**
 * Interface for extensions which provide object filter voters.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface ObjectFilterExtensionInterface
{
    /**
     * Replace the value by the filtered value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function filterValue($value);
}
