<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * The event of set optional filter type by the organizational context.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SetOrganizationalOptionalFilterTypeEvent extends Event
{
    /**
     * @var string
     */
    protected $filterType;

    /**
     * Constructor.
     *
     * @param string $filterType The optional filter type
     */
    public function __construct(string $filterType)
    {
        $this->filterType = $filterType;
    }

    /**
     * Get the optional filter type.
     *
     * @return string
     */
    public function getOptionalFilterType(): string
    {
        return $this->filterType;
    }
}
