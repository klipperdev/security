<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Sharing;

use Klipper\Component\Security\Identity\SubjectIdentityInterface;
use Klipper\Component\Security\Model\SharingInterface;

/**
 * Sharing utils.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class SharingUtils
{
    /**
     * Build the operations of sharing entry.
     *
     * @param SharingInterface $sharing The sharing entry
     *
     * @return string[]
     */
    public static function buildOperations(SharingInterface $sharing): array
    {
        $operations = [];

        foreach ($sharing->getPermissions() as $permission) {
            $operations[] = $permission->getOperation();
        }

        return $operations;
    }

    /**
     * Get the cache id of subject.
     *
     * @param SubjectIdentityInterface $subject The subject
     *
     * @return string
     */
    public static function getCacheId(SubjectIdentityInterface $subject): string
    {
        return $subject->getType().':'.$subject->getIdentifier();
    }

    /**
     * Get the cache id of sharing subject.
     *
     * @param SharingInterface $sharing The sharing entry
     *
     * @return string
     */
    public static function getSharingCacheId(SharingInterface $sharing): string
    {
        return $sharing->getSubjectClass().':'.$sharing->getSubjectId();
    }
}
