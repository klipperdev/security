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

use Klipper\Component\Security\Model\PermissionInterface;
use Klipper\Component\Security\Model\Traits\PermissionSharingEntryInterface;
use Klipper\Component\Security\Model\Traits\PermissionSharingEntryTrait;
use Klipper\Component\Security\Model\Traits\PermissionTrait;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MockPermissionSharing implements PermissionInterface, PermissionSharingEntryInterface
{
    use PermissionTrait;
    use PermissionSharingEntryTrait;

    protected ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
