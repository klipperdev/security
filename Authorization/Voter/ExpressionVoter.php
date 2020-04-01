<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Authorization\Voter;

use Klipper\Component\Security\Event\GetExpressionVariablesEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Override the Expression Voter to use Security Identity Manager to get all roles.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ExpressionVoter implements VoterInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $dispatcher         The event dispatcher
     * @param ExpressionLanguage       $expressionLanguage The expression language
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        ExpressionLanguage $expressionLanguage
    ) {
        $this->dispatcher = $dispatcher;
        $this->expressionLanguage = $expressionLanguage;
    }

    /**
     * Add the expression function provider.
     *
     * @param ExpressionFunctionProviderInterface $provider The expression function provider
     */
    public function addExpressionLanguageProvider(ExpressionFunctionProviderInterface $provider): void
    {
        $this->expressionLanguage->registerProvider($provider);
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        $result = VoterInterface::ACCESS_ABSTAIN;
        $variables = null;

        foreach ($attributes as $attribute) {
            if (!$attribute instanceof Expression) {
                continue;
            }

            if (null === $variables) {
                $variables = $this->getVariables($token, $subject);
            }

            $result = VoterInterface::ACCESS_DENIED;

            if ($this->expressionLanguage->evaluate($attribute, $variables)) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        return $result;
    }

    /**
     * Get the variables.
     *
     * @param TokenInterface $token   The token
     * @param mixed          $subject The subject to secure
     *
     * @return array
     */
    protected function getVariables(TokenInterface $token, $subject): array
    {
        $event = new GetExpressionVariablesEvent($token);
        $this->dispatcher->dispatch($event);

        $variables = array_merge($event->getVariables(), [
            'object' => $subject,
            'subject' => $subject,
        ]);

        if ($subject instanceof Request) {
            $variables['request'] = $subject;
        }

        return $variables;
    }
}
