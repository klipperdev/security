<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Doctrine\ORM\Event;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The doctrine orm get filter event.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractGetFilterEvent extends Event
{
    protected SQLFilter $filter;

    protected EntityManagerInterface $entityManager;

    protected ClassMetadata $targetEntity;

    protected string $targetTableAlias;

    protected string $sharingClass;

    protected string $filterConstraint = '';

    private ?\ReflectionProperty $refParameters = null;

    /**
     * @param SQLFilter              $filter           The sql filter
     * @param EntityManagerInterface $entityManager    The entity manager
     * @param ClassMetaData          $targetEntity     The target entity
     * @param string                 $targetTableAlias The target table alias
     * @param string                 $sharingClass     The class name of the sharing model
     */
    public function __construct(
        SqlFilter $filter,
        EntityManagerInterface $entityManager,
        ClassMetadata $targetEntity,
        string $targetTableAlias,
        string $sharingClass
    ) {
        $this->filter = $filter;
        $this->entityManager = $entityManager;
        $this->targetEntity = $targetEntity;
        $this->targetTableAlias = $targetTableAlias;
        $this->sharingClass = $sharingClass;
    }

    /**
     * Get the entity manager.
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * Get the doctrine connection.
     */
    public function getConnection(): Connection
    {
        return $this->entityManager->getConnection();
    }

    /**
     * Get the doctrine metadata of class name.
     *
     * @param string $classname The class name
     */
    public function getClassMetadata(string $classname): ClassMetadata
    {
        return $this->entityManager->getClassMetadata($classname);
    }

    /**
     * Get the doctrine metadata of sharing class.
     */
    public function getSharingClassMetadata(): ClassMetadata
    {
        return $this->getClassMetadata($this->sharingClass);
    }

    /**
     * Sets a parameter that can be used by the filter.
     *
     * @param string      $name  The name of the parameter
     * @param mixed       $value The value of the parameter
     * @param null|string $type  The parameter type
     *
     * @return static
     */
    public function setParameter(string $name, $value, ?string $type = null): self
    {
        $this->filter->setParameter($name, $value, $type);

        return $this;
    }

    /**
     * Check if a parameter was set for the filter.
     *
     * @param string $name The name of the parameter
     */
    public function hasParameter(string $name): bool
    {
        return $this->filter->hasParameter($name);
    }

    /**
     * Get a parameter to use in a query.
     *
     * @param string $name The name of the parameter
     */
    public function getParameter(string $name): string
    {
        return $this->filter->getParameter($name);
    }

    /**
     * Gets a parameter to use in a query without the output escaping.
     *
     * @param string $name The name of the parameter
     *
     * @return null|bool|bool[]|float|float[]|int|int[]|string|string[]
     *
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     */
    public function getRealParameter(string $name)
    {
        $this->getParameter($name);

        if (null === $this->refParameters) {
            $this->refParameters = new \ReflectionProperty(SQLFilter::class, 'parameters');
            $this->refParameters->setAccessible(true);
        }

        $parameters = $this->refParameters->getValue($this->filter);

        return $parameters[$name]['value'];
    }

    /**
     * Get the target entity.
     */
    public function getTargetEntity(): ClassMetadata
    {
        return $this->targetEntity;
    }

    /**
     * Get the target table alias.
     */
    public function getTargetTableAlias(): string
    {
        return $this->targetTableAlias;
    }

    /**
     * Set the filter constraint.
     *
     * @param string $filterConstraint The filter constraint
     *
     * @return static
     */
    public function setFilterConstraint(string $filterConstraint): self
    {
        $this->filterConstraint = $filterConstraint;

        return $this;
    }

    /**
     * Get the filter constraint.
     */
    public function getFilterConstraint(): string
    {
        return $this->filterConstraint;
    }
}
