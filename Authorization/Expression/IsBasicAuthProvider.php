<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Authorization\Expression;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Define some ExpressionLanguage functions.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class IsBasicAuthProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction('is_basic_auth', static function () {
                $class = '\\'.UsernamePasswordToken::class;

                return sprintf('$token && $token instanceof %1$s && !$trust_resolver->isAnonymous($token)', $class);
            }, static function (array $variables) {
                return isset($variables['token'])
                    && $variables['token'] instanceof UsernamePasswordToken
                    && !$variables['trust_resolver']->isAnonymous($variables['token']);
            }),
        ];
    }
}
