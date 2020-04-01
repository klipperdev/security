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

use Symfony\Component\Validator\Constraint;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Permission extends Constraint
{
    public $propertyClass = 'class';

    public $propertyField = 'field';

    public $invalidClassMessage = 'permission.class.invalid';

    public $requiredClassMessage = 'permission.class.required';

    public $classNotManagedMessage = 'permission.class.not_managed';

    public $invalidFieldMessage = 'permission.field.invalid';

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
