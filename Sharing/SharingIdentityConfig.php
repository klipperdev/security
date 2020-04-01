<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Sharing;

use Klipper\Component\Security\Exception\InvalidArgumentException;

/**
 * Sharing identity config.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingIdentityConfig implements SharingIdentityConfigInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var null|bool
     */
    protected $roleable;

    /**
     * @var null|bool
     */
    protected $permissible;

    /**
     * Constructor.
     *
     * @param string      $type        The type, typically, this is the PHP class name
     * @param null|string $alias       The alias of identity type
     * @param null|bool   $roleable    Check if the identity can be use the roles
     * @param null|bool   $permissible Check if the identity can be use the permissions
     */
    public function __construct(string $type, ?string $alias = null, ?bool $roleable = null, ?bool $permissible = null)
    {
        $this->type = $type;
        $this->alias = $this->buildAlias($type, $alias);
        $this->roleable = $roleable;
        $this->permissible = $permissible;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleable(): ?bool
    {
        return $this->roleable;
    }

    /**
     * {@inheritdoc}
     */
    public function isRoleable(): bool
    {
        return $this->roleable ?? false;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissible(): ?bool
    {
        return $this->permissible;
    }

    /**
     * {@inheritdoc}
     */
    public function isPermissible(): bool
    {
        return $this->permissible ?? false;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(SharingIdentityConfigInterface $newConfig): void
    {
        if ($this->getType() !== $newConfig->getType()) {
            throw new InvalidArgumentException(sprintf(
                'The sharing identity config of "%s" can be merged only with the same type, given: "%s"',
                $this->getType(),
                $newConfig->getType()
            ));
        }

        if ($this->buildAlias($this->type, null) !== ($newAlias = $newConfig->getAlias())) {
            $this->alias = $newAlias;
        }

        if (null !== $newRoleable = $newConfig->getRoleable()) {
            $this->roleable = $newRoleable;
        }

        if (null !== $newPermissible = $newConfig->getPermissible()) {
            $this->permissible = $newPermissible;
        }
    }

    /**
     * Build the alias.
     *
     * @param string      $classname The class name
     * @param null|string $alias     The alias
     */
    private function buildAlias(string $classname, ?string $alias): string
    {
        return $alias ?? strtolower(substr(strrchr($classname, '\\'), 1));
    }
}
