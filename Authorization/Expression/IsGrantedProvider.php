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

/**
 * Define some ExpressionLanguage functions.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class IsGrantedProvider implements ExpressionFunctionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction('is_granted', static function ($attributes, $object = 'null') {
                return sprintf('$auth_checker && $auth_checker->isGranted(%s, %s)', $attributes, $object);
            }, static function (array $variables, $attributes, $object = null) {
                return isset($variables['auth_checker']) && $variables['auth_checker']->isGranted($attributes, $object);
            }),
        ];
    }
}
