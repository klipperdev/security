<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Doctrine\ORM\Filter\Listener;

use Doctrine\ORM\Mapping\ClassMetadata;
use Klipper\Component\Security\Doctrine\DoctrineUtils;
use Klipper\Component\Security\Doctrine\ORM\Event\GetPrivateFilterEvent;
use Klipper\Component\Security\Model\Traits\OwnerableInterface;
use Klipper\Component\Security\Model\Traits\OwnerableOptionalInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sharing filter subscriber of Doctrine ORM SQL Filter to filter
 * the private sharing records.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PrivateSharingSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            GetPrivateFilterEvent::class => ['getFilter', 0],
        ];
    }

    /**
     * Get the sharing filter.
     *
     * @param GetPrivateFilterEvent $event The event
     */
    public function getFilter(GetPrivateFilterEvent $event): void
    {
        $filter = $this->buildSharingFilter($event);
        $filter = $this->buildOwnerFilter($event, $filter);

        $event->setFilterConstraint($filter);
    }

    /**
     * Build the query filter with sharing entries.
     *
     * @param GetPrivateFilterEvent $event The event
     */
    private function buildSharingFilter(GetPrivateFilterEvent $event): string
    {
        $targetEntity = $event->getTargetEntity();
        $targetTableAlias = $event->getTargetTableAlias();
        $connection = $event->getConnection();
        $classname = $connection->quote($targetEntity->getName());
        $meta = $event->getSharingClassMetadata();
        $identifier = DoctrineUtils::castIdentifier($targetEntity, $connection);

        return <<<SELECTCLAUSE
            {$targetTableAlias}.{$meta->getColumnName('id')} IN (SELECT
                s.{$meta->getColumnName('subjectId')}{$identifier}
            FROM
                {$meta->getTableName()} s
            WHERE
                s.{$meta->getColumnName('subjectClass')} = {$classname}
                AND s.{$meta->getColumnName('enabled')} IS TRUE
                AND (s.{$meta->getColumnName('startedAt')} IS NULL OR s.{$meta->getColumnName('startedAt')} <= CURRENT_TIMESTAMP)
                AND (s.{$meta->getColumnName('endedAt')} IS NULL OR s.{$meta->getColumnName('endedAt')} >= CURRENT_TIMESTAMP)
                AND ({$this->addWhereSecurityIdentitiesForSharing($event, $meta)})
            GROUP BY
                s.{$meta->getColumnName('subjectId')})
            SELECTCLAUSE;
    }

    /**
     * Add the where condition of security identities.
     *
     * @param GetPrivateFilterEvent $event The event
     * @param ClassMetadata         $meta  The class metadata of sharing entity
     *
     * @throws
     */
    private function addWhereSecurityIdentitiesForSharing(GetPrivateFilterEvent $event, ClassMetadata $meta): string
    {
        $where = '';
        $mapSids = (array) $event->getRealParameter('map_security_identities');
        $mapSids = !empty($mapSids) ? $mapSids : ['_without_security_identity' => 'null'];
        $connection = $event->getConnection();

        foreach ($mapSids as $type => $stringIds) {
            $where .= '' === $where ? '' : ' OR ';
            $where .= sprintf(
                '(s.%s = %s AND s.%s IN (%s))',
                $meta->getColumnName('identityClass'),
                $connection->quote($type),
                $meta->getColumnName('identityName'),
                $stringIds
            );
        }

        return $where;
    }

    /**
     * Build the query filter with owner.
     *
     * @param GetPrivateFilterEvent $event  The event
     * @param string                $filter The previous filter
     */
    private function buildOwnerFilter(GetPrivateFilterEvent $event, string $filter): string
    {
        $class = $event->getTargetEntity()->getName();
        $interfaces = class_implements($class);

        if (\in_array(OwnerableInterface::class, $interfaces, true)) {
            $filter = $this->buildRequiredOwnerFilter($event, $filter);
        } elseif (\in_array(OwnerableOptionalInterface::class, $interfaces, true)) {
            $filter = $this->buildOptionalOwnerFilter($event, $filter);
        }

        return $filter;
    }

    /**
     * Build the query filter with required owner.
     *
     * @param GetPrivateFilterEvent $event  The event
     * @param string                $filter The previous filter
     *
     * @throws
     */
    private function buildRequiredOwnerFilter(GetPrivateFilterEvent $event, string $filter): string
    {
        $connection = $event->getConnection();
        $platform = $connection->getDatabasePlatform();
        $targetEntity = $event->getTargetEntity();
        $targetTableAlias = $event->getTargetTableAlias();

        $identifier = DoctrineUtils::castIdentifier($targetEntity, $connection);
        $ownerId = $event->getRealParameter('user_id');
        $ownerColumn = $this->getAssociationColumnName($targetEntity, 'owner');
        $ownerFilter = null !== $ownerId
            ? "{$targetTableAlias}.{$ownerColumn}{$identifier} = {$connection->quote($ownerId)}"
            : (string) $platform->getIsNullExpression($targetTableAlias.'.'.$ownerColumn);

        return <<<SELECTCLAUSE
            {$ownerFilter}
                OR
            ({$filter})
            SELECTCLAUSE;
    }

    /**
     * Build the query filter with optional owner.
     *
     * @param GetPrivateFilterEvent $event  The event
     * @param string                $filter The previous filter
     *
     * @throws
     */
    private function buildOptionalOwnerFilter(GetPrivateFilterEvent $event, string $filter): string
    {
        $targetEntity = $event->getTargetEntity();
        $targetTableAlias = $event->getTargetTableAlias();
        $connection = $event->getConnection();
        $platform = $connection->getDatabasePlatform();
        $identifier = DoctrineUtils::castIdentifier($targetEntity, $connection);
        $ownerId = $event->getRealParameter('user_id');
        $ownerColumn = $this->getAssociationColumnName($targetEntity, 'owner');
        $ownerFilter = null !== $ownerId
            ? "{$targetTableAlias}.{$ownerColumn}{$identifier} = {$connection->quote($ownerId)} OR "
            : '';

        return <<<SELECTCLAUSE
            {$ownerFilter}{$platform->getIsNullExpression($targetTableAlias.'.'.$ownerColumn)}
                OR
            ({$filter})
            SELECTCLAUSE;
    }

    /**
     * Get the column name of association field name.
     *
     * @param ClassMetadata $meta      The class metadata
     * @param string        $fieldName The field name
     *
     * @throws
     */
    private function getAssociationColumnName(ClassMetadata $meta, string $fieldName): string
    {
        $mapping = $meta->getAssociationMapping($fieldName);

        return current($mapping['joinColumnFieldNames']);
    }
}
