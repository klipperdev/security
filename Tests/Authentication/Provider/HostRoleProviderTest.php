<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Authentication\Provider;

use Klipper\Component\Security\Authentication\Provider\HostRoleProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class HostRoleProviderTest extends TestCase
{
    public function testBasic(): void
    {
        /** @var TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $provider = new HostRoleProvider();

        static::assertSame($token, $provider->authenticate($token));
        static::assertFalse($provider->supports($token));
    }
}
