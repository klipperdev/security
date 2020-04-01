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
use Klipper\Component\Security\Model\GroupInterface;
use Klipper\Component\Security\Model\Traits\GroupableInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class GroupSecurityIdentity extends AbstractSecurityIdentity
{
    /**
     * Creates a group security identity from a GroupInterface.
     *
     * @param GroupInterface $group The group
     *
     * @return static
     */
    public static function fromAccount(GroupInterface $group): self
    {
        return new self(ClassUtils::getClass($group), $group->getName());
    }

    /**
     * Creates a group security identity from a TokenInterface.
     *
     * @param TokenInterface $token The token
     *
     * @throws InvalidArgumentException When the user class not implements "Klipper\Component\Security\Model\Traits\GroupableInterface"
     *
     * @return static[]
     */
    public static function fromToken(TokenInterface $token): array
    {
        $user = $token->getUser();

        if ($user instanceof GroupableInterface) {
            $sids = [];
            $groups = $user->getGroups();

            foreach ($groups as $group) {
                $sids[] = self::fromAccount($group);
            }

            return $sids;
        }

        throw new InvalidArgumentException('The user class must implement "Klipper\Component\Security\Model\Traits\GroupableInterface"');
    }
}
