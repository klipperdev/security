<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Event;

use Klipper\Component\Security\Event\SetCurrentOrganizationUserEvent;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SetCurrentOrganizationUserEventTest extends TestCase
{
    public function testEvent(): void
    {
        /** @var OrganizationUserInterface $orgUser */
        $orgUser = $this->getMockBuilder(OrganizationUserInterface::class)->getMock();

        $event = new SetCurrentOrganizationUserEvent($orgUser);

        static::assertSame($orgUser, $event->getOrganizationUser());
    }
}
