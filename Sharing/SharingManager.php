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

use Klipper\Component\Security\Exception\InvalidSubjectIdentityException;
use Klipper\Component\Security\Identity\SubjectIdentity;
use Klipper\Component\Security\Identity\SubjectIdentityInterface;
use Klipper\Component\Security\Model\RoleInterface;
use Klipper\Component\Security\Model\SharingInterface;
use Klipper\Component\Security\Permission\PermissionUtils;

/**
 * Sharing manager.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingManager extends AbstractSharingManager
{
    protected array $cacheSharing = [];

    protected array $cacheRoleSharing = [];

    protected array $cacheSubjectSharing = [];

    public function isGranted(string $operation, ?SubjectIdentityInterface $subject = null, ?string $field = null): bool
    {
        $this->preloadPermissions([$subject]);
        $this->preloadRolePermissions([$subject]);

        $sharingId = null !== $subject ? SharingUtils::getCacheId($subject) : null;
        $classAction = PermissionUtils::getMapAction($subject instanceof SubjectIdentityInterface ? $subject->getType() : null);
        $fieldAction = PermissionUtils::getMapAction($field);

        return isset($this->cacheSharing[$sharingId][$classAction][$fieldAction][$operation])
            || $this->isSharingGranted($operation, $subject, $field);
    }

    public function preloadPermissions(array $objects): self
    {
        $this->init();
        $subjects = $this->buildSubjects($objects);
        $entries = $this->buildSharingEntries($subjects);

        foreach ($subjects as $id => $subject) {
            if (isset($entries[$id])) {
                foreach ($entries[$id] as $entrySharing) {
                    if (!\array_key_exists($id, $this->cacheSubjectSharing) || false === $this->cacheSubjectSharing[$id]) {
                        $this->cacheSubjectSharing[$id] = [];
                    }

                    $operations = $this->cacheSubjectSharing[$id]['operations'] ?? [];

                    $this->cacheSubjectSharing[$id]['sharings'][] = $entrySharing;
                    $this->cacheSubjectSharing[$id]['operations'] = array_unique(array_merge(
                        $operations,
                        SharingUtils::buildOperations($entrySharing)
                    ));
                }
            }
        }

        $this->preloadPermissionsOfSharingRoles($subjects);

        return $this;
    }

    public function preloadRolePermissions(array $subjects): void
    {
        $this->init();
        $roles = [];
        $idSubjects = [];

        foreach ($subjects as $subject) {
            if ($subject instanceof SubjectIdentityInterface) {
                $subjectId = SharingUtils::getCacheId($subject);
                $idSubjects[$subjectId] = $subject;

                if (!\array_key_exists($subjectId, $this->cacheSharing)
                    && isset($this->cacheRoleSharing[$subjectId])) {
                    $roles[] = $this->cacheRoleSharing[$subjectId];
                    $this->cacheSharing[$subjectId] = [];
                }
            }
        }

        $roles = \count($roles) > 0 ? array_unique(array_merge(...$roles)) : $roles;

        if (!empty($roles)) {
            $this->doLoadSharingPermissions($idSubjects, $roles);
        }
    }

    public function resetPreloadPermissions(array $objects): self
    {
        foreach ($objects as $object) {
            try {
                $subject = SubjectIdentity::fromObject($object);
                $id = SharingUtils::getCacheId($subject);
                unset($this->cacheSharing[$id], $this->cacheRoleSharing[$id], $this->cacheSubjectSharing[$id]);
            } catch (InvalidSubjectIdentityException $e) {
                // do nothing
            } catch (\TypeError $e) {
                // do nothing
            }
        }

        return $this;
    }

    public function clear(): self
    {
        $this->cacheSharing = [];
        $this->cacheRoleSharing = [];
        $this->cacheSubjectSharing = [];

        return $this;
    }

    public function renameIdentity(string $type, string $oldName, string $newName): self
    {
        $this->provider->renameIdentity($type, $oldName, $newName);

        return $this;
    }

    public function deleteIdentity(string $type, string $name): self
    {
        $this->provider->deleteIdentity($type, $name);

        return $this;
    }

    public function deletes(array $ids): self
    {
        $this->provider->deletes($ids);

        return $this;
    }

    /**
     * Check if the access is granted by a sharing entry.
     *
     * @param string                        $operation The operation
     * @param null|SubjectIdentityInterface $subject   The subject
     * @param null|string                   $field     The field of subject
     */
    private function isSharingGranted(string $operation, ?SubjectIdentityInterface $subject = null, ?string $field = null): bool
    {
        if (null !== $subject && null === $field) {
            $id = SharingUtils::getCacheId($subject);

            return isset($this->cacheSubjectSharing[$id]['operations'])
            && \in_array($operation, $this->cacheSubjectSharing[$id]['operations'], true);
        }

        return false;
    }

    /**
     * Convert the objects into subject identities.
     *
     * @param object[] $objects The objects
     *
     * @return SubjectIdentityInterface[]
     */
    private function buildSubjects(array $objects): array
    {
        $subjects = [];

        foreach ($objects as $object) {
            if (\is_object($object)) {
                $subject = SubjectIdentity::fromObject($object);
                $id = SharingUtils::getCacheId($subject);

                if (!\array_key_exists($id, $this->cacheSubjectSharing)
                    && 'class' !== $subject->getIdentifier()
                    && $this->hasIdentityPermissible()
                    && $this->hasSharingVisibility($subject)
                    && $this->hasSubjectConfig($subject->getType())) {
                    $subjects[$id] = $subject;
                    $this->cacheSubjectSharing[$id] = false;
                }
            }
        }

        return $subjects;
    }

    /**
     * Build the sharing entries with the subject identities.
     *
     * @param SubjectIdentityInterface[] $subjects The subjects
     *
     * @return array The map of cache id and sharing instance
     */
    private function buildSharingEntries(array $subjects): array
    {
        $entries = [];

        if (!empty($subjects)) {
            $res = $this->provider->getSharingEntries(array_values($subjects));

            foreach ($res as $sharing) {
                $id = SharingUtils::getSharingCacheId($sharing);
                $entries[$id][] = $sharing;
            }
        }

        return $entries;
    }

    /**
     * Preload permissions of sharing roles.
     *
     * @param SubjectIdentityInterface[] $subjects The subjects
     */
    private function preloadPermissionsOfSharingRoles(array $subjects): void
    {
        if (!$this->hasIdentityRoleable()) {
            return;
        }

        foreach ($subjects as $id => $subject) {
            if (!isset($this->cacheRoleSharing[$id])
                    && isset($this->cacheSubjectSharing[$id]['sharings'])) {
                $this->buildCacheRoleSharing($this->cacheSubjectSharing[$id]['sharings'], $id);
            }
        }
    }

    /**
     * Build the cache role sharing.
     *
     * @param SharingInterface[] $sharings The sharing instances
     * @param string             $id       The cache id
     */
    private function buildCacheRoleSharing(array $sharings, string $id): void
    {
        $this->cacheRoleSharing[$id] = [];

        foreach ($sharings as $sharing) {
            foreach ($sharing->getRoles() as $role) {
                $this->cacheRoleSharing[$id][] = $role;
            }
        }

        $this->cacheRoleSharing[$id] = array_unique($this->cacheRoleSharing[$id]);
    }

    /**
     * Action to load the permissions of sharing roles.
     *
     * @param array    $idSubjects The map of subject id and subject
     * @param string[] $roles      The roles
     */
    private function doLoadSharingPermissions(array $idSubjects, array $roles): void
    {
        /** @var RoleInterface[] $mapRoles */
        $mapRoles = [];
        $cRoles = $this->provider->getPermissionRoles($roles);

        foreach ($cRoles as $role) {
            $mapRoles[$role->getName()] = $role;
        }

        /** @var SubjectIdentityInterface $subject */
        foreach ($idSubjects as $id => $subject) {
            foreach ($this->cacheRoleSharing[$id] as $roleId) {
                if (isset($mapRoles[$roleId])) {
                    $cRole = $mapRoles[$roleId];

                    foreach ($cRole->getPermissions() as $perm) {
                        $class = $subject->getType();
                        $field = PermissionUtils::getMapAction($perm->getField());
                        $this->cacheSharing[$id][$class][$field][$perm->getOperation()] = true;
                    }
                }
            }
        }
    }
}
