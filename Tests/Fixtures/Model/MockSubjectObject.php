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

use Klipper\Component\Security\Identity\SubjectInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MockSubjectObject implements SubjectInterface
{
    /**
     * @var null|int|string
     */
    protected $subjectIdentifier;

    /**
     * @var string
     */
    protected $name;

    /**
     * Constructor.
     *
     * @param string          $name              The name
     * @param null|int|string $subjectIdentifier The subject identifier
     */
    public function __construct(string $name, $subjectIdentifier = 42)
    {
        $this->name = $name;
        $this->subjectIdentifier = $subjectIdentifier;
    }

    public function getSubjectIdentifier()
    {
        return $this->subjectIdentifier;
    }

    /**
     * Get the name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the name.
     *
     * @param string $name The name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
