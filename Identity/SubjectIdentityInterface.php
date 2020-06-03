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

/**
 * Represents the identity of an individual subject object instance.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface SubjectIdentityInterface
{
    /**
     * Get the type of the subject. Typically, this is the PHP class name.
     */
    public function getType(): string;

    /**
     * Get the unique identifier.
     */
    public function getIdentifier(): string;

    /**
     * Get the instance of subject.
     */
    public function getObject(): ?object;

    /**
     * We specifically require this method so we can check for object equality
     * explicitly, and do not have to rely on referential equality instead.
     *
     * Though in most cases, both checks should result in the same outcome.
     *
     * Referential Equality: $subject1 === $subject2
     * Example for Subject Equality: $subject1->getId() === $subject2->getId()
     *
     * @param SubjectIdentityInterface $identity The subject identity
     */
    public function equals(SubjectIdentityInterface $identity): bool;
}
