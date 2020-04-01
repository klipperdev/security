<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Model;

/**
 * Permission interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface PermissionInterface
{
    /**
     * Get the id.
     *
     * @return null|int|string
     */
    public function getId();

    /**
     * Set the operation.
     *
     * @param null|string $operation The operation
     *
     * @return static
     */
    public function setOperation(?string $operation);

    /**
     * Get the operation.
     *
     * @return null|string
     */
    public function getOperation(): ?string;

    /**
     * Set the permission contexts.
     *
     * @param string[] $contexts The permission contexts
     *
     * @return static
     */
    public function setContexts(array $contexts);

    /**
     * Get the permission contexts.
     *
     * @return string[]
     */
    public function getContexts(): array;

    /**
     * Set the classname.
     *
     * @param null|string $class The classname
     *
     * @return static
     */
    public function setClass(?string $class);

    /**
     * Get the classname.
     *
     * @return null|string
     */
    public function getClass(): ?string;

    /**
     * Set the field.
     *
     * @param null|string $field The field
     *
     * @return static
     */
    public function setField(?string $field);

    /**
     * Get the field.
     *
     * @return null|string
     */
    public function getField(): ?string;
}
