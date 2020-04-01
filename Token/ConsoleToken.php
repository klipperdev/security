<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * ConsoleToken represents an console token.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ConsoleToken extends AbstractToken
{
    private $key;

    /**
     * Constructor.
     *
     * @param string               $key   The key shared with the authentication provider
     * @param string|UserInterface $user  The user
     * @param string[]             $roles An array of roles
     */
    public function __construct(string $key, string $user, array $roles = [])
    {
        parent::__construct($roles);

        $this->key = $key;
        $this->setUser($user);
        $this->setAuthenticated(true);
    }

    /**
     * {@inheritdoc}
     */
    public function __serialize(): array
    {
        return [$this->key, parent::__serialize()];
    }

    /**
     * {@inheritdoc}
     */
    public function __unserialize(array $data): void
    {
        [$this->key, $parentData] = $data;
        parent::__unserialize($parentData);
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(): string
    {
        return '';
    }

    /**
     * Returns the key.
     *
     * @return string The Key
     */
    public function getKey(): string
    {
        return $this->key;
    }
}
