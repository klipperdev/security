<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Fixtures\Model;

use Klipper\Component\Security\Annotation as KlipperSecurity;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MockObjectWithOnlyFieldAnnotation extends MockObject
{
    /**
     * @var string
     *
     * @KlipperSecurity\PermissionField(operations={"read", "edit"})
     */
    protected ?string $name;
}
