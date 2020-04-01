<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Expression;

use Klipper\Component\Security\Event\GetExpressionVariablesEvent;
use Klipper\Component\Security\Expression\ExpressionVariableStorage;
use Klipper\Component\Security\Identity\RoleSecurityIdentity;
use Klipper\Component\Security\Identity\SecurityIdentityManagerInterface;
use Klipper\Component\Security\Model\RoleInterface;
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRole;
use Klipper\Component\Security\Tests\Fixtures\Token\MockToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class ExpressionVariableStorageTest extends TestCase
{
    /**
     * @var AuthenticationTrustResolverInterface|MockObject
     */
    protected $trustResolver;

    /**
     * @var MockObject|SecurityIdentityManagerInterface
     */
    protected $sidManager;

    /**
     * @var OrganizationalContextInterface
     */
    protected $context;

    /**
     * @var RoleInterface
     */
    protected $orgRole;

    /**
     * @var MockObject|TokenInterface
     */
    protected $token;

    protected function setUp(): void
    {
        $this->trustResolver = $this->getMockBuilder(AuthenticationTrustResolverInterface::class)->getMock();
        $this->sidManager = $this->getMockBuilder(SecurityIdentityManagerInterface::class)->getMock();
        $this->token = $this->getMockBuilder(MockToken::class)->getMock();
    }

    public function testSetVariablesWithSecurityIdentityManager(): void
    {
        $event = new GetExpressionVariablesEvent($this->token);
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
            new RoleSecurityIdentity('role', AuthenticatedVoter::IS_AUTHENTICATED_FULLY),
        ];

        $this->token->expects(static::never())
            ->method('getRoleNames')
        ;

        $this->sidManager->expects(static::once())
            ->method('getSecurityIdentities')
            ->with($this->token)
            ->willReturn($sids)
        ;

        $variableStorage = new ExpressionVariableStorage(
            [
                'organizational_context' => $this->context,
                'organizational_role' => $this->orgRole,
            ],
            $this->sidManager
        );
        $variableStorage->add('trust_resolver', $this->trustResolver);
        $variableStorage->inject($event);

        $variables = $event->getVariables();
        static::assertCount(6, $variables);
        static::assertArrayHasKey('token', $variables);
        static::assertArrayHasKey('user', $variables);
        static::assertArrayHasKey('roles', $variables);
        static::assertArrayHasKey('trust_resolver', $variables);
        static::assertArrayHasKey('organizational_context', $variables);
        static::assertArrayHasKey('organizational_role', $variables);
        static::assertEquals(['ROLE_USER'], $variables['roles']);
        static::assertCount(1, ExpressionVariableStorage::getSubscribedEvents());
    }

    public function testSetVariablesWithoutSecurityIdentityManager(): void
    {
        $this->token->expects(static::once())
            ->method('getRoleNames')
            ->willReturn([
                'ROLE_USER',
            ])
        ;

        $event = new GetExpressionVariablesEvent($this->token);
        $variableStorage = new ExpressionVariableStorage();
        $variableStorage->add('trust_resolver', $this->trustResolver);
        $variableStorage->inject($event);

        $variables = $event->getVariables();
        static::assertCount(4, $variables);
        static::assertArrayHasKey('token', $variables);
        static::assertArrayHasKey('user', $variables);
        static::assertArrayHasKey('roles', $variables);
        static::assertArrayHasKey('trust_resolver', $variables);
        static::assertEquals(['ROLE_USER'], $variables['roles']);
        static::assertCount(1, ExpressionVariableStorage::getSubscribedEvents());
    }

    public function testHasVariable(): void
    {
        $variableStorage = new ExpressionVariableStorage([
            'foo' => 'bar',
        ]);

        static::assertFalse($variableStorage->has('bar'));
        static::assertTrue($variableStorage->has('foo'));
    }

    public function testAddVariable(): void
    {
        $variableStorage = new ExpressionVariableStorage();

        static::assertFalse($variableStorage->has('foo'));
        static::assertNull($variableStorage->get('foo'));

        $variableStorage->add('foo', 'bar');

        static::assertTrue($variableStorage->has('foo'));
        static::assertSame('bar', $variableStorage->get('foo'));
        static::assertCount(1, $variableStorage->getAll());
    }

    public function testRemoveVariable(): void
    {
        $variableStorage = new ExpressionVariableStorage([
            'foo' => 'bar',
        ]);

        static::assertTrue($variableStorage->has('foo'));

        $variableStorage->remove('foo');

        static::assertFalse($variableStorage->has('foo'));
    }
}
