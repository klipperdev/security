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
use Klipper\Component\Security\Exception\InvalidSubjectIdentityException;
use Klipper\Component\Security\Exception\UnexpectedTypeException;

/**
 * Subject identity.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class SubjectIdentity extends AbstractBaseIdentity implements SubjectIdentityInterface
{
    private ?object $subject;

    /**
     * @param string      $identifier The identifier
     * @param string      $type       The type
     * @param null|object $subject    The instance of subject
     *
     * @throws InvalidArgumentException When the identifier is empty
     * @throws InvalidArgumentException When the type is empty
     * @throws UnexpectedTypeException  When the subject instance is not an object
     */
    public function __construct(?string $type, ?string $identifier, ?object $subject = null)
    {
        parent::__construct($type, $identifier);

        $this->subject = $subject;
    }

    /**
     * Returns a textual representation of this object identity.
     */
    public function __toString(): string
    {
        return sprintf('SubjectIdentity(%s, %s)', $this->type, $this->identifier);
    }

    /**
     * Creates a subject identity for the given object.
     *
     * @param object $object The object
     *
     * @throws InvalidSubjectIdentityException
     */
    public static function fromObject(object $object): SubjectIdentityInterface
    {
        try {
            if ($object instanceof SubjectIdentityInterface) {
                return $object;
            }

            if ($object instanceof SubjectInterface) {
                return new self(ClassUtils::getClass($object), $object->getSubjectIdentifier(), $object);
            }

            if (method_exists($object, 'getId')) {
                return new self(ClassUtils::getClass($object), (string) ($object->getId() ?: 'class'), $object);
            }
        } catch (InvalidArgumentException $e) {
            throw new InvalidSubjectIdentityException($e->getMessage(), 0, $e);
        }

        throw new InvalidSubjectIdentityException('The object must either implement the SubjectInterface, or have a method named "getId"');
    }

    /**
     * Creates a subject identity for the given class name.
     *
     * @param string $class The class name
     *
     * @return static
     */
    public static function fromClassname(?string $class): SubjectIdentityInterface
    {
        try {
            if (null === $class || !class_exists($class)) {
                throw new InvalidArgumentException(sprintf('The class "%s" does not exist', $class));
            }

            return new self(ClassUtils::getRealClass($class), 'class');
        } catch (InvalidArgumentException $e) {
            throw new InvalidSubjectIdentityException($e->getMessage(), 0, $e);
        }
    }

    public function getObject(): ?object
    {
        return $this->subject;
    }

    public function equals(SubjectIdentityInterface $identity): bool
    {
        return $this->identifier === $identity->getIdentifier()
               && $this->type === $identity->getType();
    }
}
