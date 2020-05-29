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

use Klipper\Component\Security\Authorization\Expression\IsGrantedProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class IsGrantedProviderTest extends TestCase
{
    public function testIsBasicAuth(): void
    {
        $object = new \stdClass();
        $authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();

        $authChecker->expects(static::once())
            ->method('isGranted')
            ->with('perm:view', $object)
            ->willReturn(true)
        ;

        $expressionLanguage = new ExpressionLanguage(null, [new IsGrantedProvider()]);
        $variables = [
            'object' => $object,
            'auth_checker' => $authChecker,
        ];

        static::assertTrue($expressionLanguage->evaluate('is_granted("perm:view", object)', $variables));

        $compiled = '$auth_checker && $auth_checker->isGranted("perm:view", $object)';
        static::assertEquals($compiled, $expressionLanguage->compile('is_granted("perm:view", object)', ['object']));
    }
}
