<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Organizational;

use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\Traits\OrganizationalInterface;

/**
 * Organizational Utils.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class OrganizationalUtil
{
    /**
     * Format the name with the organization name in suffix.
     *
     * @param object|OrganizationalInterface $object The organizational object
     * @param string                         $name   The name
     */
    public static function formatName($object, string $name): string
    {
        return $object instanceof OrganizationalInterface
            ? static::formatNameWithOrg($name, $object->getOrganization())
            : $name;
    }

    /**
     * Format the name with the organization name in suffix.
     *
     * @param string                     $name         The name
     * @param null|OrganizationInterface $organization The organization
     */
    public static function formatNameWithOrg(string $name, ?OrganizationInterface $organization = null): string
    {
        if ($organization instanceof OrganizationInterface
                && false === strpos('__', $name)) {
            $name .= '__'.$organization->getName();
        }

        return $name;
    }

    /**
     * Format the organizational name without suffix.
     *
     * @param string $name The name
     */
    public static function format(string $name): string
    {
        if (false !== ($pos = strrpos($name, '__'))) {
            $name = substr($name, 0, $pos);
        }

        return $name;
    }

    /**
     * Get the organization suffix.
     *
     * @param string $name The name
     */
    public static function getSuffix(string $name): string
    {
        return false !== ($pos = strrpos($name, '__'))
            ? substr($name, $pos)
            : '';
    }
}
