<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Model\Traits;

use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockUserOrganizationUsers;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class OrganizationalTraitTest extends TestCase
{
    public function testModel(): void
    {
        /** @var MockObject|OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        $org->expects(static::once())
            ->method('getId')
            ->willReturn(42)
        ;

        $user = new MockUserOrganizationUsers();

        static::assertNull($user->getOrganization());

        $user->setOrganization($org);
        static::assertSame($org, $user->getOrganization());
        static::assertSame(42, $user->getOrganizationId());
    }
}
