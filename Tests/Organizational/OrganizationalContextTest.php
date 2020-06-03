<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Organizational;

use Klipper\Component\Security\Event\SetCurrentOrganizationEvent;
use Klipper\Component\Security\Event\SetCurrentOrganizationUserEvent;
use Klipper\Component\Security\Event\SetOrganizationalOptionalFilterTypeEvent;
use Klipper\Component\Security\Exception\RuntimeException;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\Security\Organizational\OrganizationalContext;
use Klipper\Component\Security\OrganizationalTypes;
use Klipper\Component\Security\Tests\Fixtures\Model\MockUserOrganizationUsers;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class OrganizationalContextTest extends TestCase
{
    /**
     * @var MockObject|TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var MockObject|TokenInterface
     */
    protected $token;

    /**
     * @var EventDispatcherInterface|MockObject
     */
    protected $dispatcher;

    protected ?OrganizationalContext $context = null;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $this->dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $this->context = new OrganizationalContext($this->tokenStorage, $this->dispatcher);

        $this->tokenStorage->expects(static::any())
            ->method('getToken')
            ->willReturn($this->token)
        ;
    }

    public function testSetDisabledCurrentOrganization(): void
    {
        $this->dispatcher->expects(static::once())
            ->method('dispatch')
            ->with(new SetCurrentOrganizationEvent(false))
        ;

        $this->context->setCurrentOrganization(false);

        static::assertNull($this->context->getCurrentOrganization());
    }

    public function testSetCurrentOrganization(): void
    {
        /** @var OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();

        $this->dispatcher->expects(static::once())
            ->method('dispatch')
            ->with(new SetCurrentOrganizationEvent($org))
        ;

        $this->context->setCurrentOrganization($org);
        static::assertSame($org, $this->context->getCurrentOrganization());
    }

    public function testGetCurrentOrganizationWithoutSetterAndWithTokenUser(): void
    {
        /** @var OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        $user = $this->getMockBuilder(MockUserOrganizationUsers::class)->getMock();

        $this->dispatcher->expects(static::never())
            ->method('dispatch')
        ;

        $this->token->expects(static::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $user->expects(static::once())
            ->method('getOrganization')
            ->willReturn($org)
        ;

        static::assertSame($org, $this->context->getCurrentOrganization());
    }

    public function testGetCurrentOrganizationWithoutSetterAndWithTokenUserAndEmptyOrganization(): void
    {
        $user = $this->getMockBuilder(MockUserOrganizationUsers::class)->getMock();

        $this->dispatcher->expects(static::never())
            ->method('dispatch')
        ;

        $this->token->expects(static::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $user->expects(static::once())
            ->method('getOrganization')
            ->willReturn(null)
        ;

        static::assertNull($this->context->getCurrentOrganization());
    }

    public function testGetCurrentOrganizationWithoutSetterAndWithTokenUserWithoutOrganizationField(): void
    {
        $user = $this->getMockBuilder(UserInterface::class)->getMock();

        $this->dispatcher->expects(static::never())
            ->method('dispatch')
        ;

        $this->token->expects(static::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        static::assertNull($this->context->getCurrentOrganization());
    }

    public function testGetCurrentOrganizationWithoutSetterAndWithoutTokenUser(): void
    {
        $this->dispatcher->expects(static::never())
            ->method('dispatch')
        ;

        $this->token->expects(static::once())
            ->method('getUser')
            ->willReturn(null)
        ;

        static::assertNull($this->context->getCurrentOrganization());
    }

    public function testSetCurrentOrganizationUser(): void
    {
        /** @var MockObject|OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        /** @var MockObject|OrganizationUserInterface $orgUser */
        $orgUser = $this->getMockBuilder(OrganizationUserInterface::class)->getMock();
        /** @var MockObject|UserInterface $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();

        $this->dispatcher->expects(static::at(0))
            ->method('dispatch')
            ->with(new SetCurrentOrganizationEvent($org))
        ;

        $this->dispatcher->expects(static::at(1))
            ->method('dispatch')
            ->with(new SetCurrentOrganizationUserEvent($orgUser))
        ;

        $this->token->expects(static::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $orgUser->expects(static::atLeast(2))
            ->method('getUser')
            ->willReturn($user)
        ;

        $orgUser->expects(static::once())
            ->method('getOrganization')
            ->willReturn($org)
        ;

        $user->expects(static::atLeast(2))
            ->method('getUsername')
            ->willReturn('user.test')
        ;

        $this->context->setCurrentOrganization($org);
        $this->context->setCurrentOrganizationUser($orgUser);

        static::assertSame($orgUser, $this->context->getCurrentOrganizationUser());
        static::assertSame($org, $this->context->getCurrentOrganization());
    }

    public function testIsOrganization(): void
    {
        /** @var MockObject|OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        /** @var MockObject|OrganizationUserInterface $orgUser */
        $orgUser = $this->getMockBuilder(OrganizationUserInterface::class)->getMock();
        /** @var MockObject|UserInterface $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();

        $this->dispatcher->expects(static::at(0))
            ->method('dispatch')
            ->with(new SetCurrentOrganizationEvent($org))
        ;

        $this->dispatcher->expects(static::at(1))
            ->method('dispatch')
            ->with(new SetCurrentOrganizationUserEvent($orgUser))
        ;

        $this->token->expects(static::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $orgUser->expects(static::atLeast(2))
            ->method('getUser')
            ->willReturn($user)
        ;

        $orgUser->expects(static::once())
            ->method('getOrganization')
            ->willReturn($org)
        ;

        $user->expects(static::atLeast(2))
            ->method('getUsername')
            ->willReturn('user.test')
        ;

        $org->expects(static::once())
            ->method('isUserOrganization')
            ->willReturn(false)
        ;

        $this->context->setCurrentOrganization($org);
        $this->context->setCurrentOrganizationUser($orgUser);

        static::assertTrue($this->context->isOrganization());
    }

    public function testSetOptionalFilterType(): void
    {
        static::assertSame(OrganizationalTypes::OPTIONAL_FILTER_WITH_ORG, $this->context->getOptionalFilterType());
        static::assertFalse($this->context->isOptionalFilterType(OrganizationalTypes::OPTIONAL_FILTER_ALL));

        $this->dispatcher->expects(static::at(0))
            ->method('dispatch')
            ->with(new SetOrganizationalOptionalFilterTypeEvent(OrganizationalTypes::OPTIONAL_FILTER_ALL))
        ;

        $this->context->setOptionalFilterType(OrganizationalTypes::OPTIONAL_FILTER_ALL);

        static::assertSame(OrganizationalTypes::OPTIONAL_FILTER_ALL, $this->context->getOptionalFilterType());
        static::assertTrue($this->context->isOptionalFilterType(OrganizationalTypes::OPTIONAL_FILTER_ALL));
    }

    public function testValidEmptyTokenForUser(): void
    {
        /** @var MockObject|TokenStorageInterface $tokenStorage */
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $tokenStorage->expects(static::atLeastOnce())
            ->method('getToken')
            ->willReturn(null)
        ;

        $context = new OrganizationalContext($tokenStorage);
        $context->setCurrentOrganization(null);
        static::assertNull($context->getCurrentOrganization());
    }

    public function testInvalidTokenForUser(): void
    {
        $this->expectException(\Klipper\Component\Security\Exception\RuntimeException::class);
        $this->expectExceptionMessage('The current organization cannot be added in security token because the security token is empty');

        /** @var OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();

        /** @var MockObject|TokenStorageInterface $tokenStorage */
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $tokenStorage->expects(static::once())
            ->method('getToken')
            ->willReturn(null)
        ;

        $context = new OrganizationalContext($tokenStorage);
        $context->setCurrentOrganization($org);
    }

    public function testValidEmptyTokenForOrganizationUser(): void
    {
        /** @var MockObject|TokenStorageInterface $tokenStorage */
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $tokenStorage->expects(static::atLeastOnce())
            ->method('getToken')
            ->willReturn(null)
        ;

        $this->dispatcher->expects(static::never())
            ->method('dispatch')
        ;

        $context = new OrganizationalContext($tokenStorage);
        $context->setCurrentOrganizationUser(null);
        static::assertNull($context->getCurrentOrganizationUser());
    }

    public function testInvalidTokenForOrganizationUser(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The current organization user cannot be added in security token because the security token is empty');

        /** @var OrganizationUserInterface $orgUser */
        $orgUser = $this->getMockBuilder(OrganizationUserInterface::class)->getMock();

        /** @var MockObject|TokenStorageInterface $tokenStorage */
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $tokenStorage->expects(static::once())
            ->method('getToken')
            ->willReturn(null)
        ;

        $this->dispatcher->expects(static::never())
            ->method('dispatch')
        ;

        $context = new OrganizationalContext($tokenStorage);
        $context->setCurrentOrganizationUser($orgUser);
    }
}
