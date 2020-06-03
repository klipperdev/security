<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Doctrine\ORM\Listener;

use Doctrine\Common\EventSubscriber;
use Klipper\Component\Security\Exception\SecurityException;
use Klipper\Component\Security\Permission\PermissionManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Abstract doctrine listener class.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractListener implements EventSubscriber
{
    protected ?TokenStorageInterface $tokenStorage = null;

    protected ?PermissionManagerInterface $permissionManager = null;

    protected bool $initialized = false;

    /**
     * Set the token storage.
     *
     * @param TokenStorageInterface $tokenStorage The token storage
     *
     * @return static
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage): self
    {
        $this->tokenStorage = $tokenStorage;

        return $this;
    }

    /**
     * Gets security token storage.
     *
     * @throws
     */
    public function getTokenStorage(): TokenStorageInterface
    {
        $this->init();

        return $this->tokenStorage;
    }

    /**
     * Set the permission manager.
     *
     * @param PermissionManagerInterface $permissionManager The permission manager
     *
     * @return static
     */
    public function setPermissionManager(PermissionManagerInterface $permissionManager): self
    {
        $this->permissionManager = $permissionManager;

        return $this;
    }

    /**
     * Get the Permission Manager.
     *
     * @throws
     */
    public function getPermissionManager(): PermissionManagerInterface
    {
        $this->init();

        return $this->permissionManager;
    }

    /**
     * Init listener.
     *
     * @throws SecurityException
     */
    protected function init(): void
    {
        if (!$this->initialized) {
            $msg = 'The "%s()" method must be called before the init of the "%s" class';

            foreach ($this->getInitProperties() as $property => $setterMethod) {
                if (null === $this->{$property}) {
                    throw new SecurityException(sprintf($msg, $setterMethod, static::class));
                }
            }

            $this->initialized = true;
        }
    }

    /**
     * Get the map of properties and methods required on the init.
     */
    abstract protected function getInitProperties(): array;
}
