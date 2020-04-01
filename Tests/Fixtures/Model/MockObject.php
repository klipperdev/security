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

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MockObject
{
    /**
     * @var null|int|string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * Constructor.
     *
     * @param string          $name The name
     * @param null|int|string $id   The id
     */
    public function __construct(?string $name, $id = 42)
    {
        $this->name = $name;
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the name.
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the name.
     *
     * @param string $name The name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}
