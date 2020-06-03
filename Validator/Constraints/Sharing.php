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
class Sharing extends Constraint
{
    public string $subjectClass = 'subjectClass';

    public string $identityClass = 'identityClass';

    public string $roles = 'roles';

    public string $permissions = 'permissions';

    public string $invalidClassMessage = 'sharing.class.invalid';

    public string $classNotManagedMessage = 'sharing.class.not_managed';

    public string $identityNotRoleableMessage = 'sharing.class.identity_not_roleable';

    public string $identityNotPermissibleMessage = 'sharing.class.identity_not_permissible';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
