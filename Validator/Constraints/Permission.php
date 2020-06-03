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
    public string $propertyClass = 'class';

    public string $propertyField = 'field';

    public string $invalidClassMessage = 'permission.class.invalid';

    public string $requiredClassMessage = 'permission.class.required';

    public string $classNotManagedMessage = 'permission.class.not_managed';

    public string $invalidFieldMessage = 'permission.field.invalid';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
