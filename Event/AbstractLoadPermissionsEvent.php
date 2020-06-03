<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Event;

use Klipper\Component\Security\Identity\SecurityIdentityInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The abstract load permissions event.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractLoadPermissionsEvent extends Event
{
    /**
     * @var SecurityIdentityInterface[]
     */
    protected array $sids;

    /**
     * @var string[]
     */
    protected array $roles;

    /**
     * @param SecurityIdentityInterface[] $sids  The security identities
     * @param string[]                    $roles The role names
     */
    public function __construct(array $sids, array $roles)
    {
        $this->sids = $sids;
        $this->roles = $roles;
    }

    /**
     * Get the security identities.
     *
     * @return SecurityIdentityInterface[]
     */
    public function getSecurityIdentities(): array
    {
        return $this->sids;
    }

    /**
     * Get the roles.
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }
}
