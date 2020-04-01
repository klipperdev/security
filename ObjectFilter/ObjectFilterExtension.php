<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\ObjectFilter;

/**
 * Object filter extension for add the object filter voter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ObjectFilterExtension implements ObjectFilterExtensionInterface
{
    /**
     * @var ObjectFilterVoterInterface[]
     */
    protected $voters;

    /**
     * Constructor.
     *
     * @param ObjectFilterVoterInterface[] $voters The object filter voters
     */
    public function __construct(array $voters)
    {
        $this->voters = $voters;
    }

    /**
     * {@inheritdoc}
     */
    public function filterValue($value)
    {
        $val = null;

        foreach ($this->voters as $voter) {
            if ($voter->supports($value)) {
                $val = $voter->getValue($value);

                break;
            }
        }

        return $val;
    }
}
