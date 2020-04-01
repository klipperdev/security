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

use Klipper\Component\Security\Event\SetOrganizationalOptionalFilterTypeEvent;
use Klipper\Component\Security\OrganizationalTypes;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SetOrganizationalOptionalFilterTypeEventTest extends TestCase
{
    public function testEvent(): void
    {
        $type = OrganizationalTypes::OPTIONAL_FILTER_ALL;

        $event = new SetOrganizationalOptionalFilterTypeEvent($type);

        static::assertSame($type, $event->getOptionalFilterType());
    }
}
