<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Event;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The get expression variables event.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class GetExpressionVariablesEvent extends Event
{
    protected TokenInterface $token;

    /**
     * @var array<string, mixed>
     */
    protected array $variables;

    /**
     * @param TokenInterface       $token     The token
     * @param array<string, mixed> $variables The variables
     */
    public function __construct(TokenInterface $token, array $variables = [])
    {
        $this->token = $token;
        $this->variables = $variables;
    }

    /**
     * Get the token.
     */
    public function getToken(): TokenInterface
    {
        return $this->token;
    }

    /**
     * Add variables in the expression language evaluate variables.
     *
     * @param array<string, mixed> $variables The variables
     */
    public function addVariables(array $variables): void
    {
        foreach ($variables as $name => $value) {
            $this->addVariable($name, $value);
        }
    }

    /**
     * Add a variable in the expression language evaluate variables.
     *
     * @param string $name  The name of expression variable
     * @param mixed  $value The value of expression variable
     */
    public function addVariable(string $name, $value): void
    {
        $this->variables[$name] = $value;
    }

    /**
     * Get the variables.
     */
    public function getVariables(): array
    {
        return $this->variables;
    }
}
