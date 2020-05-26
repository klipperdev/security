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
use Klipper\Component\Security\Model\Traits\PermissionTrait;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MockPermission implements PermissionInterface
{
    use PermissionTrait;

    protected ?int $id = null;

    /**
     * {@inheritdoc}
     */
    public function getId(): ?int
    {
        return $this->id;
    }
}
