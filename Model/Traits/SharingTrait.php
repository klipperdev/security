<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Model\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Klipper\Component\Security\Model\PermissionInterface;

/**
 * Trait for sharing model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait SharingTrait
{
    use RoleableTrait;

    /**
     * @ORM\Column(type="string", length=244)
     */
    protected ?string $subjectClass = null;

    /**
     * @ORM\Column(type="string", length=36)
     */
    protected ?string $subjectId = null;

    /**
     * @ORM\Column(type="string", length=244)
     */
    protected ?string $identityClass = null;

    /**
     * @ORM\Column(type="string", length=244)
     */
    protected ?string $identityName = null;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $enabled = true;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?\DateTimeInterface $startedAt = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?\DateTimeInterface $endedAt = null;

    /**
     * @var null|Collection|PermissionInterface[]
     *
     * @ORM\ManyToMany(
     *     targetEntity="Klipper\Component\Security\Model\PermissionInterface",
     *     inversedBy="sharingEntries"
     * )
     */
    protected ?Collection $permissions = null;

    /**
     * {@inheritdoc}
     */
    public function setSubjectClass(?string $class): self
    {
        $this->subjectClass = $class;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectClass(): ?string
    {
        return $this->subjectClass;
    }

    /**
     * {@inheritdoc}
     */
    public function setSubjectId(?string $id): self
    {
        $this->subjectId = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectId(): ?string
    {
        return $this->subjectId;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentityClass(?string $class): self
    {
        $this->identityClass = $class;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityClass(): ?string
    {
        return $this->identityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentityName(?string $name): self
    {
        $this->identityName = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityName(): ?string
    {
        return $this->identityName;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setStartedAt(?\DateTimeInterface $date): self
    {
        $this->startedAt = $date;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setEndedAt(?\DateTimeInterface $date): self
    {
        $this->endedAt = $date;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEndedAt(): ?\DateTimeInterface
    {
        return $this->endedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions(): Collection
    {
        return $this->permissions ?: $this->permissions = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function hasPermission(PermissionInterface $permission): bool
    {
        return $this->getPermissions()->contains($permission);
    }

    /**
     * {@inheritdoc}
     */
    public function addPermission(PermissionInterface $permission): self
    {
        if (!$this->getPermissions()->contains($permission)) {
            $this->getPermissions()->add($permission);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removePermission(PermissionInterface $permission): self
    {
        if ($this->getPermissions()->contains($permission)) {
            $this->getPermissions()->removeElement($permission);
        }

        return $this;
    }
}
