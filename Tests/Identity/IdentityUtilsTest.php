<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Identity;

use Klipper\Component\Security\Identity\IdentityUtils;
use Klipper\Component\Security\Identity\RoleSecurityIdentity;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRole;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class IdentityUtilsTest extends TestCase
{
    public function testMerge(): void
    {
        $role1 = new RoleSecurityIdentity(MockRole::class, 'ROLE_USER');
        $role2 = new RoleSecurityIdentity(MockRole::class, 'ROLE_ADMIN');
        $role3 = new RoleSecurityIdentity(MockRole::class, 'ROLE_USER');
        $role4 = new RoleSecurityIdentity(MockRole::class, 'ROLE_FOO');

        $sids = [$role1, $role2];
        $newSids = [$role3, $role4];
        $valid = [$role1, $role2, $role4];

        $sids = IdentityUtils::merge($sids, $newSids);

        static::assertEquals($valid, $sids);
    }
}
