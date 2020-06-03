<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Doctrine\ORM\Filter\Listener;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Query\FilterCollection;
use Klipper\Component\Security\Doctrine\ORM\Event\GetPrivateFilterEvent;
use Klipper\Component\Security\Doctrine\ORM\Filter\Listener\PrivateSharingSubscriber;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObject;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObjectOwnerable;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObjectOwnerableOptional;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRole;
use Klipper\Component\Security\Tests\Fixtures\Model\MockSharing;
use Klipper\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class PrivateSharingSubscriberTest extends TestCase
{
    /**
     * @var EntityManagerInterface|MockObject
     */
    protected $entityManager;

    /**
     * @var Connection|MockObject
     */
    protected $connection;

    /**
     * @var ClassMetadata|MockObject
     */
    protected $targetEntity;

    /**
     * @var ClassMetadata|MockObject
     */
    protected $sharingMeta;

    /**
     * @var MockObject|SQLFilter
     */
    protected $filter;

    protected ?GetPrivateFilterEvent $event = null;

    protected ?PrivateSharingSubscriber $listener = null;

    /**
     * @throws
     */
    protected function setUp(): void
    {
        $this->entityManager = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $this->targetEntity = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $this->sharingMeta = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $this->filter = $this->getMockForAbstractClass(SQLFilter::class, [$this->entityManager]);
        $this->event = new GetPrivateFilterEvent(
            $this->filter,
            $this->entityManager,
            $this->targetEntity,
            't0',
            MockSharing::class
        );
        $this->listener = new PrivateSharingSubscriber();

        $this->entityManager->expects(static::any())
            ->method('getFilters')
            ->willReturn(new FilterCollection($this->entityManager))
        ;

        $this->entityManager->expects(static::any())
            ->method('getConnection')
            ->willReturn($this->connection)
        ;

        $this->connection->expects(static::any())
            ->method('getDatabasePlatform')
            ->willReturn($this->getMockForAbstractClass(AbstractPlatform::class))
        ;

        $this->entityManager->expects(static::any())
            ->method('getClassMetadata')
            ->with(MockSharing::class)
            ->willReturn($this->sharingMeta)
        ;

        $this->connection->expects(static::any())
            ->method('quote')
            ->willReturnCallback(static function ($v) {
                if (\is_array($v)) {
                    return implode(', ', $v);
                }

                return '\''.$v.'\'';
            })
        ;

        static::assertCount(1, PrivateSharingSubscriber::getSubscribedEvents());
    }

    public function testGetFilter(): void
    {
        $this->injectParameters(true, 42, [
            MockRole::class => '\'ROLE_USER\'',
            MockUserRoleable::class => '\'user.test\'',
        ]);

        $this->targetEntity->expects(static::any())
            ->method('getName')
            ->willReturn(MockObject::class)
        ;

        $this->sharingMeta->expects(static::once())
            ->method('getTableName')
            ->willReturn('test_sharing')
        ;

        $this->sharingMeta->expects(static::atLeastOnce())
            ->method('getColumnName')
            ->willReturnCallback(static function ($value) {
                $map = [
                    'subjectClass' => 'subject_class',
                    'subjectId' => 'subject_id',
                    'identityClass' => 'identity_class',
                    'identityName' => 'identity_name',
                    'enabled' => 'enabled',
                    'startedAt' => 'started_at',
                    'endedAt' => 'ended_at',
                    'id' => 'id',
                ];

                return $map[$value] ?? null;
            })
        ;

        $validFilter = <<<'SELECTCLAUSE'
            t0.id IN (SELECT
                s.subject_id
            FROM
                test_sharing s
            WHERE
                s.subject_class = 'Klipper\Component\Security\Tests\Fixtures\Model\MockObject'
                AND s.enabled IS TRUE
                AND (s.started_at IS NULL OR s.started_at <= CURRENT_TIMESTAMP)
                AND (s.ended_at IS NULL OR s.ended_at >= CURRENT_TIMESTAMP)
                AND ((s.identity_class = 'Klipper\Component\Security\Tests\Fixtures\Model\MockRole' AND s.identity_name IN ('ROLE_USER')) OR (s.identity_class = 'Klipper\Component\Security\Tests\Fixtures\Model\MockUserRoleable' AND s.identity_name IN ('user.test')))
            GROUP BY
                s.subject_id)
            SELECTCLAUSE;

        $this->listener->getFilter($this->event);
        static::assertSame($validFilter, $this->event->getFilterConstraint());
    }

    public function getCurrentUserValues(): array
    {
        return [
            [MockObjectOwnerable::class, false],
            [MockObjectOwnerable::class, true],
            [MockObjectOwnerableOptional::class, false],
            [MockObjectOwnerableOptional::class, true],
        ];
    }

    /**
     * @dataProvider getCurrentUserValues
     */
    public function testGetFilterWithOwnerableObject(string $objectClass, bool $withCurrentUser): void
    {
        $this->injectParameters(
            true,
            $withCurrentUser ? 50 : null,
            [
                MockRole::class => '\'ROLE_USER\'',
                MockUserRoleable::class => '\'user.test\'',
            ]
        );

        $this->targetEntity->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn($objectClass)
        ;

        $this->targetEntity->expects(static::atLeastOnce())
            ->method('getAssociationMapping')
            ->willReturnCallback(static function ($value) {
                $map = [
                    'owner' => [
                        'joinColumnFieldNames' => [
                            'owner' => 'owner_id',
                        ],
                    ],
                ];

                return $map[$value] ?? null;
            })
        ;

        $this->sharingMeta->expects(static::once())
            ->method('getTableName')
            ->willReturn('test_sharing')
        ;

        $this->sharingMeta->expects(static::atLeastOnce())
            ->method('getColumnName')
            ->willReturnCallback(static function ($value) {
                $map = [
                    'subjectClass' => 'subject_class',
                    'subjectId' => 'subject_id',
                    'identityClass' => 'identity_class',
                    'identityName' => 'identity_name',
                    'enabled' => 'enabled',
                    'startedAt' => 'started_at',
                    'endedAt' => 'ended_at',
                    'id' => 'id',
                ];

                return $map[$value] ?? null;
            })
        ;

        $ownerFilter = $withCurrentUser
            ? 't0.owner_id = \'50\''
            : 't0.owner_id IS NULL';

        if ($withCurrentUser && MockObjectOwnerableOptional::class === $objectClass) {
            $ownerFilter .= ' OR t0.owner_id IS NULL';
        }

        $validFilter = <<<SELECTCLAUSE
            {$ownerFilter}
                OR
            (t0.id IN (SELECT
                s.subject_id
            FROM
                test_sharing s
            WHERE
                s.subject_class = '{$objectClass}'
                AND s.enabled IS TRUE
                AND (s.started_at IS NULL OR s.started_at <= CURRENT_TIMESTAMP)
                AND (s.ended_at IS NULL OR s.ended_at >= CURRENT_TIMESTAMP)
                AND ((s.identity_class = 'Klipper\\Component\\Security\\Tests\\Fixtures\\Model\\MockRole' AND s.identity_name IN ('ROLE_USER')) OR (s.identity_class = 'Klipper\\Component\\Security\\Tests\\Fixtures\\Model\\MockUserRoleable' AND s.identity_name IN ('user.test')))
            GROUP BY
                s.subject_id))
            SELECTCLAUSE;

        $this->listener->getFilter($this->event);
        static::assertSame($validFilter, $this->event->getFilterConstraint());
    }

    /**
     * @param mixed $sharingEnabled
     * @param mixed $userId
     */
    protected function injectParameters($sharingEnabled = true, $userId = 42, array $mapSids = []): void
    {
        $this->filter->setParameter('has_security_identities', !empty($mapSids), 'boolean');
        $this->filter->setParameter('map_security_identities', $mapSids, 'array');
        $this->filter->setParameter('user_id', $userId, 'integer');
        $this->filter->setParameter('sharing_manager_enabled', $sharingEnabled, 'boolean');
    }
}
