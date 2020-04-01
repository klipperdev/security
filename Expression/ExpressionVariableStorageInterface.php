<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Expression;

use Klipper\Component\Security\Event\GetExpressionVariablesEvent;

/**
 * Expression variable storage interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface ExpressionVariableStorageInterface
{
    /**
     * Add a variable in the expression language evaluate variables.
     *
     * @param string $name  The name of expression variable
     * @param mixed  $value The value of expression variable
     *
     * @return static
     */
    public function add(string $name, $value);

    /**
     * Remove a variable.
     *
     * @param string $name The variable name
     *
     * @return static
     */
    public function remove(string $name);

    /**
     * Check if the variable is defined.
     *
     * @param string $name The variable name
     */
    public function has(string $name): bool;

    /**
     * Get the value of variable.
     *
     * @param string $name The variable name
     *
     * @return null|mixed
     */
    public function get(string $name);

    /**
     * Get all variables.
     */
    public function getAll(): array;

    /**
     * Inject the expression variables in event.
     *
     * @param GetExpressionVariablesEvent $event The event
     */
    public function inject(GetExpressionVariablesEvent $event): void;
}
