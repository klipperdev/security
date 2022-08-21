<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Identity;

use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Klipper\Component\Security\Exception\InvalidArgumentException;
use Klipper\Component\Security\Model\RoleInterface;
use Klipper\Component\Security\Model\Traits\RoleableInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class RoleSecurityIdentity extends AbstractSecurityIdentity
{
    /**
     * Creates a role security identity from a RoleInterface.
     *
     * @param RoleInterface|string $role The role
     *
     * @return static
     */
    public static function fromAccount($role): self
    {
        return $role instanceof RoleInterface
            ? new self(ClassUtils::getClass($role), $role->getName())
            : new self('role', $role);
    }

    /**
     * Creates a role security identity from a TokenInterface.
     *
     * @param TokenInterface $token The token
     *
     * @throws InvalidArgumentException When the user class not implements "Klipper\Component\Security\Model\Traits\RoleableInterface"
     *
     * @return static[]
     */
    public static function fromToken(TokenInterface $token): array
    {
        $user = $token->getUser();

        if ($user instanceof RoleableInterface) {
            $sids = [];
            $roles = $user->getRoles();

            foreach ($roles as $role) {
                $sids[] = self::fromAccount($role);
            }

            return $sids;
        }

        throw new InvalidArgumentException('The user class must implement "Klipper\Component\Security\Model\Traits\RoleableInterface"');
    }

    public function isRole(): bool
    {
        return 0 !== strpos($this->getIdentifier(), 'IS_') && 0 !== strpos($this->getIdentifier(), 'PUBLIC_ACCESS');
    }
}
