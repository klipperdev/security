<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Validator\Constraints;

use Doctrine\Common\Collections\Collection;
use Klipper\Component\Security\Identity\SubjectIdentity;
use Klipper\Component\Security\Sharing\SharingIdentityConfigInterface;
use Klipper\Component\Security\Sharing\SharingManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingValidator extends ConstraintValidator
{
    protected SharingManagerInterface $sharingManager;

    protected PropertyAccessorInterface $propertyAccessor;

    /**
     * @param SharingManagerInterface        $sharingManager   The sharing manager
     * @param null|PropertyAccessorInterface $propertyAccessor The property access
     */
    public function __construct(
        SharingManagerInterface $sharingManager,
        ?PropertyAccessorInterface $propertyAccessor = null
    ) {
        $this->sharingManager = $sharingManager;
        $this->propertyAccessor = $propertyAccessor ?? PropertyAccess::createPropertyAccessor();
    }

    public function validate($value, Constraint $constraint): void
    {
        /** @var Sharing $constraint */
        $subjectClass = $this->propertyAccessor->getValue($value, $constraint->subjectClass);
        $roles = $this->propertyAccessor->getValue($value, $constraint->roles);
        $identityClass = $this->propertyAccessor->getValue($value, $constraint->identityClass);
        $permissions = $this->propertyAccessor->getValue($value, $constraint->permissions);

        $this->validateSubject($constraint, $subjectClass);
        $this->validateIdentity($constraint, $identityClass, $roles, $permissions);
    }

    /**
     * Validate the subject.
     *
     * @param Sharing $constraint   The sharing constraint
     * @param string  $subjectClass The subject class
     */
    private function validateSubject(Sharing $constraint, ?string $subjectClass): void
    {
        $res = $this->validateClass($constraint, $subjectClass, $constraint->subjectClass);

        if ($res && !$this->sharingManager->hasSharingVisibility(SubjectIdentity::fromClassname($subjectClass))) {
            $this->context->buildViolation($constraint->classNotManagedMessage)
                ->atPath($constraint->subjectClass)
                ->setParameter('%class_property%', $constraint->subjectClass)
                ->setParameter('%class%', $subjectClass)
                ->addViolation()
            ;
        }
    }

    /**
     * Validate the identity.
     *
     * @param Sharing    $constraint    The sharing constraint
     * @param string     $identityClass The identity class
     * @param string[]   $roles         The roles
     * @param Collection $permissions   The permissions
     */
    private function validateIdentity(Sharing $constraint, ?string $identityClass, array $roles, Collection $permissions): void
    {
        $res = $this->validateClass($constraint, $identityClass, $constraint->identityClass);

        if ($res && !$this->sharingManager->hasIdentityConfig($identityClass)) {
            $res = false;
            $this->context->buildViolation($constraint->classNotManagedMessage)
                ->atPath($constraint->identityClass)
                ->setParameter('%class_property%', $constraint->identityClass)
                ->setParameter('%class%', $identityClass)
                ->addViolation()
            ;
        }

        if ($res) {
            $config = $this->sharingManager->getIdentityConfig($identityClass);
            $this->validateRoles($constraint, $config, $roles);
            $this->validatePermissions($constraint, $config, $permissions);
        }
    }

    /**
     * Validate the roles field.
     *
     * @param Sharing                        $constraint The sharing constraint
     * @param SharingIdentityConfigInterface $config     The sharing identity config
     * @param string[]                       $roles      The roles
     */
    private function validateRoles(
        Sharing $constraint,
        SharingIdentityConfigInterface $config,
        array $roles
    ): void {
        if (!empty($roles) && !$config->isPermissible()) {
            $this->context->buildViolation($constraint->identityNotRoleableMessage)
                ->atPath($constraint->roles)
                ->setParameter('%class_property%', $constraint->identityClass)
                ->setParameter('%class%', $config->getType())
                ->addViolation()
            ;
        }
    }

    /**
     * Validate the permissions field.
     *
     * @param Sharing                        $constraint  The sharing constraint
     * @param SharingIdentityConfigInterface $config      The sharing identity config
     * @param Collection                     $permissions The permissions
     */
    private function validatePermissions(
        Sharing $constraint,
        SharingIdentityConfigInterface $config,
        Collection $permissions
    ): void {
        if ($permissions->count() > 0 && !$config->isPermissible()) {
            $this->context->buildViolation($constraint->identityNotPermissibleMessage)
                ->atPath($constraint->permissions)
                ->setParameter('%class_property%', $constraint->identityClass)
                ->setParameter('%class%', $config->getType())
                ->addViolation()
            ;
        }
    }

    /**
     * Validate the class.
     *
     * @param Sharing $constraint   The sharing constraint
     * @param string  $class        The class
     * @param string  $propertyPath The property path
     */
    private function validateClass(Sharing $constraint, ?string $class, string $propertyPath): bool
    {
        if (!class_exists($class)) {
            $this->context->buildViolation($constraint->invalidClassMessage)
                ->atPath($propertyPath)
                ->setParameter('%class_property%', $propertyPath)
                ->setParameter('%class%', (string) $class)
                ->addViolation()
            ;

            return false;
        }

        return true;
    }
}
