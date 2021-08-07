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
use Klipper\Component\Security\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class UserSecurityIdentity extends AbstractSecurityIdentity
{
    /**
     * Creates a user security identity from a UserInterface.
     *
     * @param UserInterface $user The user
     */
    public static function fromAccount(UserInterface $user): UserSecurityIdentity
    {
        return new self(ClassUtils::getClass($user), $user->getUserIdentifier());
    }

    /**
     * Creates a user security identity from a TokenInterface.
     *
     * @param TokenInterface $token The token
     *
     * @throws InvalidArgumentException When the user class not implements "Klipper\Component\Security\Model\UserInterface"
     */
    public static function fromToken(TokenInterface $token): UserSecurityIdentity
    {
        $user = $token->getUser();

        if ($user instanceof UserInterface) {
            return self::fromAccount($user);
        }

        throw new InvalidArgumentException('The user class must implement "Klipper\Component\Security\Model\UserInterface"');
    }
}
