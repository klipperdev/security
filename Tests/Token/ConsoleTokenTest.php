<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Token;

use Klipper\Component\DefaultValue\Tests\Fixtures\Object\User;
use Klipper\Component\Security\Token\ConsoleToken;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class ConsoleTokenTest extends TestCase
{
    public function testConsoleToken(): void
    {
        $token = new ConsoleToken('key', new User('username', ''), [
            'ROLE_TEST',
        ]);

        static::assertSame('', $token->getCredentials());
        static::assertSame('key', $token->getKey());

        $tokenSerialized = $token->serialize();
        $value = \is_string($tokenSerialized);
        static::assertTrue($value);

        $token2 = new ConsoleToken('', new User('test', ''));
        $token2->unserialize($tokenSerialized);

        static::assertEquals($token, $token2);
    }
}
