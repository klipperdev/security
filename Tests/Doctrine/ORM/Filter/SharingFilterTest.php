<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Doctrine\ORM\Filter;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\FilterCollection;
use Klipper\Component\Security\Doctrine\ORM\Event\GetPrivateFilterEvent;
use Klipper\Component\Security\Doctrine\ORM\Filter\SharingFilter;
use Klipper\Component\Security\Identity\SubjectIdentity;
use Klipper\Component\Security\Sharing\SharingManagerInterface;
use Klipper\Component\Security\SharingVisibilities;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObject;
use Klipper\Component\Security\Tests\Fixtures\Model\MockSharing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SharingFilterTest extends TestCase
{
    /**
     * @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $em;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|SharingManagerInterface
     */
    protected $sharingManager;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var string
     */
    protected $sharingClass;

    /**
     * @var ClassMetadata|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $targetEntity;

    /**
     * @var SharingFilter
     */
    protected $filter;

    /**
     * @throws
     */
    protected function setUp(): void
    {
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->eventManager = new EventManager();
        $this->sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $this->eventDispatcher = new EventDispatcher();
        $this->sharingClass = MockSharing::class;
        $this->targetEntity = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->setMethods([
            'getName',
        ])->getMock();
        $this->filter = new SharingFilter($this->em);

        $connection = $this->getMockBuilder(Connection::class)->getMock();

        $this->em->expects(static::any())
            ->method('getEventManager')
            ->willReturn($this->eventManager)
        ;

        $this->em->expects(static::any())
            ->method('getFilters')
            ->willReturn(new FilterCollection($this->em))
        ;

        $this->em->expects(static::any())
            ->method('getConnection')
            ->willReturn($connection)
        ;

        $connection->expects(static::any())
            ->method('quote')
            ->willReturnCallback(static function ($v) {
                return '\''.$v.'\'';
            })
        ;

        $this->targetEntity->expects(static::any())
            ->method('getName')
            ->willReturn(MockObject::class)
        ;
    }

    public function testAddFilterConstraintWithoutSupports(): void
    {
        $this->eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $this->eventDispatcher->expects(static::never())
            ->method('dispatch')
        ;

        $this->filter->addFilterConstraint($this->targetEntity, 't');
    }

    public function testAddFilterConstraint(): void
    {
        $this->filter->setSharingManager($this->sharingManager);
        $this->filter->setSharingClass($this->sharingClass);
        $this->filter->setEventDispatcher($this->eventDispatcher);
        $this->filter->setParameter('has_security_identities', true, 'boolean');
        $this->filter->setParameter('map_security_identities', [], 'array');
        $this->filter->setParameter('user_id', 42, 'integer');
        $this->filter->setParameter('sharing_manager_enabled', true, 'boolean');

        $this->eventDispatcher->addListener(
            GetPrivateFilterEvent::class,
            static function (GetPrivateFilterEvent $event): void {
                $event->setFilterConstraint('FILTER_TEST');
            }
        );

        $this->sharingManager->expects(static::once())
            ->method('hasSharingVisibility')
            ->with(SubjectIdentity::fromClassname(MockObject::class))
            ->willReturn(true)
        ;

        $this->sharingManager->expects(static::once())
            ->method('getSharingVisibility')
            ->with(SubjectIdentity::fromClassname(MockObject::class))
            ->willReturn(SharingVisibilities::TYPE_PRIVATE)
        ;

        static::assertSame('FILTER_TEST', $this->filter->addFilterConstraint($this->targetEntity, 't'));
    }
}
