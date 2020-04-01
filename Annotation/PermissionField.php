<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Annotation;

use Klipper\Component\Config\Annotation\AbstractAnnotation;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @Annotation
 * @Target({"CLASS", "ANNOTATION", "PROPERTY"})
 */
class PermissionField extends AbstractAnnotation
{
    /**
     * @var null|string[]
     */
    protected $operations = [];

    /**
     * @var null|string[]
     */
    protected $mappingPermissions = [];

    /**
     * @var null|bool
     */
    protected $editable;

    /**
     * @return string[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * @param string[] $operations
     */
    public function setOperations(array $operations): void
    {
        $this->operations = $operations;
    }

    /**
     * @return string[]
     */
    public function getMappingPermissions(): array
    {
        return $this->mappingPermissions;
    }

    /**
     * @param string[] $mappingPermissions
     */
    public function setMappingPermissions(array $mappingPermissions): void
    {
        $this->mappingPermissions = $mappingPermissions;
    }

    /**
     * @return null|bool
     */
    public function getEditable(): ?bool
    {
        return $this->editable;
    }

    /**
     * @param null|bool $editable
     */
    public function setEditable(?bool $editable): void
    {
        $this->editable = $editable;
    }
}
