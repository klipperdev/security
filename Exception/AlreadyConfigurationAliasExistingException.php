<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Exception;

/**
 * AlreadyConfigurationAliasExistingException for the Security component.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class AlreadyConfigurationAliasExistingException extends InvalidArgumentException
{
    /**
     * @param string $alias The alias
     * @param string $class The class name
     */
    public function __construct(string $alias, string $class)
    {
        parent::__construct(sprintf('The alias "%s" of sharing identity configuration for the class "%s" already exist', $alias, $class));
    }
}
