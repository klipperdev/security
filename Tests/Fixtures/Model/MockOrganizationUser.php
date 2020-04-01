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

use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\Model\Traits\OrganizationUserTrait;
use Klipper\Component\Security\Model\UserInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MockOrganizationUser implements OrganizationUserInterface
{
    use OrganizationUserTrait;

    /**
     * @var null|int
     */
    protected $id;

    /**
     * Constructor.
     *
     * @param OrganizationInterface $organization The organization
     * @param UserInterface         $user         The user
     * @param int                   $id           The id
     */
    public function __construct(OrganizationInterface $organization, UserInterface $user, int $id = 42)
    {
        $this->organization = $organization;
        $this->user = $user;
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): ?int
    {
        return $this->id;
    }
}
