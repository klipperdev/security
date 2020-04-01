<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Expression;

use Klipper\Component\Security\Event\GetExpressionVariablesEvent;
use Klipper\Component\Security\Identity\IdentityUtils;
use Klipper\Component\Security\Identity\SecurityIdentityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Variable storage of expression.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ExpressionVariableStorage implements ExpressionVariableStorageInterface, EventSubscriberInterface
{
    /**
     * @var null|SecurityIdentityManagerInterface
     */
    private $sim;

    /**
     * @var array<string, mixed>
     */
    private $variables = [];

    /**
     * Constructor.
     *
     * @param array<string, mixed>                  $variables The expression variables
     * @param null|SecurityIdentityManagerInterface $sim       The security identity manager
     */
    public function __construct(
        array $variables = [],
        ?SecurityIdentityManagerInterface $sim = null
    ) {
        $this->sim = $sim;

        foreach ($variables as $name => $value) {
            $this->add($name, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            GetExpressionVariablesEvent::class => ['inject', 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $name, $value): self
    {
        $this->variables[$name] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $name): self
    {
        unset($this->variables[$name]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return isset($this->variables[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name)
    {
        return $this->has($name)
            ? $this->variables[$name]
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        return $this->variables;
    }

    /**
     * {@inheritdoc}
     */
    public function inject(GetExpressionVariablesEvent $event): void
    {
        $token = $event->getToken();

        $event->addVariables(array_merge($this->variables, [
            'token' => $token,
            'user' => $token->getUser(),
            'roles' => $this->getAllRoles($token),
        ]));
    }

    /**
     * Get all roles.
     *
     * @param TokenInterface $token The token
     *
     * @return string[]
     */
    private function getAllRoles(TokenInterface $token): array
    {
        if (null !== $this->sim) {
            $sids = $this->sim->getSecurityIdentities($token);

            return IdentityUtils::filterRolesIdentities($sids);
        }

        return $token->getRoleNames();
    }
}
