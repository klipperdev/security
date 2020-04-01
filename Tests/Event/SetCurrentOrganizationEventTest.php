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

use Klipper\Component\Security\Event\SetCurrentOrganizationEvent;
use Klipper\Component\Security\Model\OrganizationInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SetCurrentOrganizationEventTest extends TestCase
{
    public function testEvent(): void
    {
        /** @var OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();

        $event = new SetCurrentOrganizationEvent($org);

        static::assertSame($org, $event->getOrganization());
    }
}
