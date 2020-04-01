<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\ObjectFilter;

/**
 * Object Filter Interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface ObjectFilterInterface
{
    /**
     * Get object filter unit of work.
     */
    public function getUnitOfWork(): UnitOfWorkInterface;

    /**
     * Begin the transaction.
     */
    public function beginTransaction(): void;

    /**
     * Execute the transaction.
     */
    public function commit(): void;

    /**
     * Filtering the object fields with null value for unauthorized access field.
     *
     * @param object $object The object instance
     *
     * @throws \InvalidArgumentException When $object is not a object instance
     */
    public function filter($object): void;

    /**
     * Restoring the object fields with old value for unauthorized access field.
     *
     * @param object $object The object instance
     *
     * @throws \InvalidArgumentException When $object is not a object instance
     */
    public function restore($object): void;
}
