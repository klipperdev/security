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
 * Object Filter Unit Of Work.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class UnitOfWork implements UnitOfWorkInterface
{
    /**
     * Map of the original object data of managed objects.
     * Keys are object ids (spl_object_hash). This is used for calculating changesets.
     */
    private array $originalObjectData = [];

    public function getObjectIdentifiers(): array
    {
        return array_keys($this->originalObjectData);
    }

    /**
     * @throws
     */
    public function attach(object $object): void
    {
        $oid = spl_object_hash($object);

        if (\array_key_exists($oid, $this->originalObjectData)) {
            return;
        }

        $this->originalObjectData[$oid] = [];
        $ref = new \ReflectionClass($object);

        foreach ($ref->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($object);

            $this->originalObjectData[$oid][$property->getName()] = $value;
        }
    }

    public function detach(object $object): void
    {
        $oid = spl_object_hash($object);

        if (!\array_key_exists($oid, $this->originalObjectData)) {
            return;
        }

        unset($this->originalObjectData[$oid]);
    }

    /**
     * @throws
     */
    public function getObjectChangeSet(object $object): array
    {
        $oid = spl_object_hash($object);

        if (!\array_key_exists($oid, $this->originalObjectData)) {
            return [];
        }

        $changeSet = [];
        $ref = new \ReflectionClass($object);

        foreach ($ref->getProperties() as $property) {
            $property->setAccessible(true);
            $oldValue = $this->originalObjectData[$oid][$property->getName()];
            $newValue = $property->getValue($object);

            if ($newValue !== $oldValue) {
                $changeSet[$property->getName()] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changeSet;
    }

    public function flush(): void
    {
        $this->originalObjectData = [];
    }
}
