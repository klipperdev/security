<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Authorization\Expression;

use Klipper\Component\Security\Authorization\Expression\IsBasicAuthProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class IsBasicAuthProviderTest extends TestCase
{
    public function testIsBasicAuth(): void
    {
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $token = new UsernamePasswordToken($user, 'test', ['ROLE_USER']);
        $trustResolver = new AuthenticationTrustResolver();

        $expressionLanguage = new ExpressionLanguage(null, [new IsBasicAuthProvider()]);
        $variables = [
            'token' => $token,
            'trust_resolver' => $trustResolver,
        ];

        static::assertTrue($expressionLanguage->evaluate('is_basic_auth()', $variables));

        $compiled = '$token && $token instanceof \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken && $trust_resolver->isAuthenticated($token)';
        static::assertEquals($compiled, $expressionLanguage->compile('is_basic_auth()'));
    }
}
