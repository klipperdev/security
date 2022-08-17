<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Fixtures\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MockToken extends AbstractToken
{
    public function getCredentials(): string
    {
        return '';
    }
}
