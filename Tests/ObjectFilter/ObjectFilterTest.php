<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\ObjectFilter;

use Klipper\Component\Security\Event\ObjectFieldViewGrantedEvent;
use Klipper\Component\Security\Event\ObjectViewGrantedEvent;
use Klipper\Component\Security\Event\PostCommitObjectFilterEvent;
use Klipper\Component\Security\Event\PreCommitObjectFilterEvent;
use Klipper\Component\Security\Event\RestoreViewGrantedEvent;
use Klipper\Component\Security\ObjectFilter\ObjectFilter;
use Klipper\Component\Security\ObjectFilter\ObjectFilterExtensionInterface;
use Klipper\Component\Security\ObjectFilter\UnitOfWorkInterface;
use Klipper\Component\Security\Permission\FieldVote;
use Klipper\Component\Security\Permission\PermissionManagerInterface;
use Klipper\Component\Security\Permission\PermVote;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class ObjectFilterTest extends TestCase
{
    /**
     * @var ObjectFilter
     */
    protected $of;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|UnitOfWorkInterface
     */
    private $uow;

    /**
     * @var ObjectFilterExtensionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $ofe;

    /**
     * @var PermissionManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $pm;

    /**
     * @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $ac;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    protected function setUp(): void
    {
        $this->uow = $this->getMockBuilder(UnitOfWorkInterface::class)->getMock();
        $this->ofe = $this->getMockBuilder(ObjectFilterExtensionInterface::class)->getMock();
        $this->pm = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
        $this->ac = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();
        $this->dispatcher = new EventDispatcher();

        $this->of = new ObjectFilter($this->ofe, $this->pm, $this->ac, $this->dispatcher, $this->uow);
    }

    public function testGetUnitOfWork(): void
    {
        static::assertSame($this->uow, $this->of->getUnitOfWork());
    }

    public function testCommitEvents(): void
    {
        $preEventAction = false;
        $postEventAction = false;
        $objects = [];

        $this->dispatcher->addListener(PreCommitObjectFilterEvent::class, function (PreCommitObjectFilterEvent $event) use (&$objects, &$preEventAction): void {
            $preEventAction = true;
            $this->assertSame($objects, $event->getObjects());
        });

        $this->dispatcher->addListener(PostCommitObjectFilterEvent::class, function (PostCommitObjectFilterEvent $event) use (&$objects, &$postEventAction): void {
            $postEventAction = true;
            $this->assertSame($objects, $event->getObjects());
        });

        $this->pm->expects(static::once())
            ->method('preloadPermissions')
            ->with($objects)
        ;

        $this->of->commit();

        static::assertTrue($preEventAction);
        static::assertTrue($postEventAction);
    }

    public function testFilter(): void
    {
        $object = new MockObject('foo');

        $this->prepareFilterTest($object);

        $this->ac->expects(static::once())
            ->method('isGranted')
            ->willReturn(false)
        ;

        $this->of->filter($object);

        static::assertNull($object->getName());
    }

    public function testFilterTransactional(): void
    {
        $object = new MockObject('foo');

        $this->prepareFilterTest($object);

        $this->ac->expects(static::once())
            ->method('isGranted')
            ->willReturn(false)
        ;

        $this->of->beginTransaction();
        $this->of->filter($object);
        $this->of->commit();

        static::assertSame(42, $object->getId());
        static::assertNull($object->getName());
    }

    public function testFilterSkipAuthorizationChecker(): void
    {
        $eventAction = 0;
        $object = new MockObject('foo');

        $this->prepareFilterTest($object);

        $this->ac->expects(static::never())
            ->method('isGranted')
        ;

        $this->dispatcher->addListener(ObjectViewGrantedEvent::class, static function (ObjectViewGrantedEvent $event) use (&$eventAction): void {
            ++$eventAction;
            $event->setGranted(true);
        });

        $this->dispatcher->addListener(ObjectFieldViewGrantedEvent::class, static function (ObjectFieldViewGrantedEvent $event) use (&$eventAction): void {
            ++$eventAction;
            $event->setGranted(false);
        });

        $this->of->filter($object);

        static::assertSame(2, $eventAction);
        static::assertNull($object->getName());
    }

    public function testFilterWithInvalidType(): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "object", "integer" given');

        /** @var object $object */
        $object = 42;

        $this->of->filter($object);
    }

    public function testRestore(): void
    {
        $object = new MockObject('foo');

        $this->prepareRestoreTest($object);

        $this->ac->expects(static::once())
            ->method('isGranted')
            ->willReturn(false)
        ;

        $this->of->restore($object);

        static::assertSame('bar', $object->getName());
    }

    public function testRestoreTransactional(): void
    {
        $object = new MockObject('foo');

        $this->uow->expects(static::once())
            ->method('attach')
            ->with($object)
        ;

        $this->uow->expects(static::once())
            ->method('getObjectChangeSet')
            ->with($object)
            ->willReturn([
                'name' => [
                    'old' => 'bar',
                    'new' => 'foo',
                ],
            ])
        ;

        $this->pm->expects(static::once())
            ->method('preloadPermissions')
            ->with([$object])
        ;

        $this->ac->expects(static::once())
            ->method('isGranted')
            ->willReturn(false)
        ;

        $this->of->beginTransaction();
        $this->of->restore($object);
        $this->of->commit();

        static::assertSame('bar', $object->getName());
    }

    public function testRestoreSkipAuthorizationChecker(): void
    {
        $eventAction = false;
        $object = new MockObject('foo');

        $this->prepareRestoreTest($object);

        $this->ac->expects(static::never())
            ->method('isGranted')
        ;

        $this->dispatcher->addListener(RestoreViewGrantedEvent::class, static function (RestoreViewGrantedEvent $event) use (&$eventAction): void {
            $eventAction = true;
            $event->setGranted(false);
        });

        $this->of->restore($object);

        static::assertTrue($eventAction);
        static::assertSame('bar', $object->getName());
    }

    public function getRestoreActions(): array
    {
        return [
            [false, false, null, 'foo', null],
            [false, false, 'bar', 'foo', 'bar'],
            [false, false, 'bar', null, 'bar'],

            [true, false, null, 'foo', null],
            [true, false, 'bar', 'foo', 'bar'],
            [true, false, 'bar', null, 'bar'],

            [true, true, null, 'foo', 'foo'],
            [true, true, 'bar', 'foo', 'foo'],
            [true, true, 'bar', null, null],
        ];
    }

    /**
     * @dataProvider getRestoreActions
     *
     * @param bool  $allowView  Check if the user is allowed to view the object
     * @param bool  $allowEdit  Check if the user is allowed to edit the object
     * @param mixed $oldValue   The object old value
     * @param mixed $newValue   The object new value
     * @param mixed $validValue The valid object value
     */
    public function testRestoreByAction($allowView, $allowEdit, $oldValue, $newValue, $validValue): void
    {
        $object = new MockObject($newValue);
        $fv = new FieldVote($object, 'name');

        $this->prepareRestoreTest($object, [
            'name' => [
                'old' => $oldValue,
                'new' => $newValue,
            ],
        ]);

        $this->ac->expects(static::at(0))
            ->method('isGranted')
            ->with(new PermVote('read'), $fv)
            ->willReturn($allowView)
        ;

        if ($allowView) {
            $this->ac->expects(static::at(1))
                ->method('isGranted')
                ->with(new PermVote('edit'), $fv)
                ->willReturn($allowEdit)
            ;
        }

        $this->of->restore($object);

        static::assertSame($validValue, $object->getName());
    }

    public function testExcludedClasses(): void
    {
        $this->of->setExcludedClasses([
            MockObject::class,
        ]);

        $object = new MockObject('foo');

        $this->uow->expects(static::never())
            ->method('attach')
        ;

        $this->ofe->expects(static::never())
            ->method('filterValue')
        ;

        $this->pm->expects(static::never())
            ->method('preloadPermissions')
        ;

        $this->ac->expects(static::never())
            ->method('isGranted')
        ;

        $this->of->filter($object);

        static::assertNotNull($object->getName());
    }

    public function testRestoreWithInvalidType(): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "object", "integer" given');

        /** @var object $object */
        $object = 42;

        $this->of->restore($object);
    }

    /**
     * Prepare the restore test.
     *
     * @param object $object The mock object
     */
    protected function prepareFilterTest($object): void
    {
        $this->uow->expects(static::once())
            ->method('attach')
            ->with($object)
        ;

        $this->ofe->expects(static::once())
            ->method('filterValue')
            ->willReturn(null)
        ;

        $this->pm->expects(static::once())
            ->method('preloadPermissions')
            ->with([$object])
        ;
    }

    /**
     * Prepare the restore test.
     *
     * @param object     $object    The mock object
     * @param null|array $changeSet The field change set
     */
    protected function prepareRestoreTest($object, $changeSet = null): void
    {
        if (null === $changeSet) {
            $changeSet = [
                'name' => [
                    'old' => 'bar',
                    'new' => 'foo',
                ],
            ];
        }

        $this->pm->expects(static::once())
            ->method('preloadPermissions')
            ->with([$object])
        ;

        $this->uow->expects(static::once())
            ->method('attach')
            ->with($object)
        ;

        $this->uow->expects(static::once())
            ->method('getObjectChangeSet')
            ->with($object)
            ->willReturn($changeSet)
        ;
    }
}
