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
 * @KlipperSecurity\Permission(
 *     operations={"view", "create", "update", "delete"},
 *     fields={
 *         "id": @KlipperSecurity\PermissionField(operations={"read"})
 *     }
 * )
 *
 * @KlipperSecurity\Permission(
 *     master="foo",
 *     fields={
 *         "id": @KlipperSecurity\PermissionField(operations={"view"})
 *     }
 * )
 *
 * @KlipperSecurity\SharingSubject(
 *     visibility="public"
 * )
 *
 * @KlipperSecurity\SharingSubject(
 *     visibility="private"
 * )
 *
 * @KlipperSecurity\SharingIdentity(
 *     alias="object"
 * )
 *
 * @KlipperSecurity\SharingIdentity(
 *     roleable="true",
 *     permissible="true"
 * )
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MockObjectWithAnnotation extends MockObject
{
    /**
     * @var string
     *
     * @KlipperSecurity\PermissionField(operations={"read"})
     *
     * @KlipperSecurity\PermissionField(operations={"edit"})
     */
    protected ?string $name;
}
