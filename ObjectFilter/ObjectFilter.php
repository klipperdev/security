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

use Klipper\Component\Security\Event\ObjectFieldViewGrantedEvent;
use Klipper\Component\Security\Event\ObjectViewGrantedEvent;
use Klipper\Component\Security\Event\PostCommitObjectFilterEvent;
use Klipper\Component\Security\Event\PreCommitObjectFilterEvent;
use Klipper\Component\Security\Event\RestoreViewGrantedEvent;
use Klipper\Component\Security\Exception\UnexpectedTypeException;
use Klipper\Component\Security\Permission\FieldVote;
use Klipper\Component\Security\Permission\PermissionManagerInterface;
use Klipper\Component\Security\Permission\PermVote;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Object Filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ObjectFilter implements ObjectFilterInterface
{
    /**
     * @var UnitOfWorkInterface
     */
    private $uow;

    /**
     * @var ObjectFilterExtensionInterface
     */
    private $ofe;

    /**
     * @var PermissionManagerInterface
     */
    private $pm;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $ac;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var string[]
     */
    private $excludedClasses = [];

    /**
     * If the action filtering/restoring is in a transaction, then the action
     * will be executing on the commit.
     *
     * @var bool
     */
    private $isTransactional = false;

    /**
     * The object list not analyzed (empty after commit).
     *
     * @var array
     */
    private $queue = [];

    /**
     * The object ids of object to filter (empty after commit).
     *
     * @var array
     */
    private $toFilter = [];

    /**
     * Constructor.
     *
     * @param ObjectFilterExtensionInterface $ofe        The object filter extension
     * @param PermissionManagerInterface     $pm         The permission manager
     * @param AuthorizationCheckerInterface  $ac         The authorization checker
     * @param EventDispatcherInterface       $dispatcher The event dispatcher
     * @param null|UnitOfWorkInterface       $uow        The unit of work
     */
    public function __construct(
        ObjectFilterExtensionInterface $ofe,
        PermissionManagerInterface $pm,
        AuthorizationCheckerInterface $ac,
        EventDispatcherInterface $dispatcher,
        ?UnitOfWorkInterface $uow = null
    ) {
        $this->uow = $uow ?? new UnitOfWork();
        $this->ofe = $ofe;
        $this->pm = $pm;
        $this->ac = $ac;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Set the excluded classes.
     *
     * @param string[] $excludedClasses The excluded classes
     */
    public function setExcludedClasses(array $excludedClasses): void
    {
        $this->excludedClasses = $excludedClasses;
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitOfWork(): UnitOfWorkInterface
    {
        return $this->uow;
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction(): void
    {
        $this->isTransactional = true;
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): void
    {
        $event = new PreCommitObjectFilterEvent($this->queue);
        $this->dispatcher->dispatch($event);

        $this->pm->preloadPermissions(array_values($this->queue));

        foreach ($this->queue as $id => $object) {
            if (\in_array($id, $this->toFilter, true)) {
                $this->doFilter($object);
            } else {
                $this->doRestore($object);
            }
        }

        $event = new PostCommitObjectFilterEvent($this->queue);
        $this->dispatcher->dispatch($event);

        $this->queue = [];
        $this->isTransactional = false;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($object): void
    {
        if (!\is_object($object)) {
            throw new UnexpectedTypeException($object, 'object');
        }

        if ($this->isExcludedClass($object)) {
            return;
        }

        $id = spl_object_hash($object);

        $this->uow->attach($object);
        $this->queue[$id] = $object;
        $this->toFilter[] = $id;

        if (!$this->isTransactional) {
            $this->commit();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function restore($object): void
    {
        if (!\is_object($object)) {
            throw new UnexpectedTypeException($object, 'object');
        }

        $this->uow->attach($object);
        $this->queue[spl_object_hash($object)] = $object;

        if (!$this->isTransactional) {
            $this->commit();
        }
    }

    /**
     * Executes the filtering.
     *
     * @param object $object
     *
     * @throws
     */
    protected function doFilter($object): void
    {
        $clearAll = false;
        $id = spl_object_hash($object);
        array_splice($this->toFilter, array_search($id, $this->toFilter, true), 1);

        if (!$this->isViewGranted($object)) {
            $clearAll = true;
        }

        $ref = new \ReflectionClass($object);

        foreach ($ref->getProperties() as $property) {
            $property->setAccessible(true);
            $fieldVote = new FieldVote($object, $property->getName());
            $value = $property->getValue($object);

            if ($this->isFilterViewGranted($fieldVote, $value, $clearAll)) {
                $value = $this->ofe->filterValue($value);
                $property->setValue($object, $value);
            }
        }
    }

    /**
     * Executes the restoring.
     *
     * @param object $object
     *
     * @throws
     */
    protected function doRestore($object): void
    {
        $changeSet = $this->uow->getObjectChangeSet($object);
        $ref = new \ReflectionClass($object);

        foreach ($changeSet as $field => $values) {
            $fv = new FieldVote($object, $field);

            if ($this->isRestoreViewGranted($fv, $values)) {
                $property = $ref->getProperty($field);
                $property->setAccessible(true);
                $property->setValue($object, $values['old']);
            }
        }
    }

    /**
     * Check if the field value must be filtered.
     *
     * @param FieldVote $fieldVote The field vote
     * @param mixed     $value     The value
     * @param bool      $clearAll  Check if all fields must be filtered
     */
    protected function isFilterViewGranted(FieldVote $fieldVote, $value, bool $clearAll): bool
    {
        return null !== $value
            && !$this->isIdentifier($fieldVote, $value)
            && ($clearAll || !$this->isViewGranted($fieldVote));
    }

    /**
     * Check if the field value must be restored.
     *
     * @param FieldVote $fieldVote The field vote
     * @param array     $values    The map of old and new values
     */
    protected function isRestoreViewGranted(FieldVote $fieldVote, array $values): bool
    {
        $event = new RestoreViewGrantedEvent($fieldVote, $values['old'], $values['new']);
        $this->dispatcher->dispatch($event);

        if ($event->isSkipAuthorizationChecker()) {
            return !$event->isGranted();
        }

        return !$this->ac->isGranted(new PermVote('read'), $fieldVote)
            || !$this->ac->isGranted(new PermVote('edit'), $fieldVote);
    }

    /**
     * Check if the object or object field can be seen.
     *
     * @param FieldVote|object $object The object or field vote
     */
    protected function isViewGranted($object): bool
    {
        if ($object instanceof FieldVote) {
            $event = new ObjectFieldViewGrantedEvent($object);
            $permission = new PermVote('read');
        } else {
            $event = new ObjectViewGrantedEvent($object);
            $permission = new PermVote('view');
        }

        $this->dispatcher->dispatch($event);

        if ($event->isSkipAuthorizationChecker()) {
            return $event->isGranted();
        }

        return $this->ac->isGranted($permission, $object);
    }

    /**
     * Check if the field is an identifier.
     *
     * @param FieldVote $fieldVote The field vote
     * @param mixed     $value     The value
     */
    protected function isIdentifier(FieldVote $fieldVote, $value): bool
    {
        return (\is_int($value) || \is_string($value))
                && (string) $value === $fieldVote->getSubject()->getIdentifier()
                && \in_array($fieldVote->getField(), ['id', 'subjectIdentifier'], true);
    }

    /**
     * Check if the object is an excluded class.
     *
     * @param object $object The object
     */
    protected function isExcludedClass($object): bool
    {
        foreach ($this->excludedClasses as $excludedClass) {
            if ($object instanceof $excludedClass) {
                return true;
            }
        }

        return false;
    }
}
