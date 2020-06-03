<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Authorization\Voter;

use Klipper\Component\Security\Authorization\Voter\ExpressionVoter;
use Klipper\Component\Security\Expression\ExpressionVariableStorage;
use Klipper\Component\Security\Identity\RoleSecurityIdentity;
use Klipper\Component\Security\Identity\SecurityIdentityManagerInterface;
use Klipper\Component\Security\Model\RoleInterface;
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRole;
use Klipper\Component\Security\Tests\Fixtures\Token\MockToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class ExpressionVoterTest extends TestCase
{
    protected ?EventDispatcher $dispatcher = null;

    /**
     * @var ExpressionLanguage|MockObject
     */
    protected $expressionLanguage;

    /**
     * @var AuthenticationTrustResolverInterface|MockObject
     */
    protected $trustResolver;

    /**
     * @var MockObject|SecurityIdentityManagerInterface
     */
    protected $sidManager;

    /**
     * @var MockObject|OrganizationalContextInterface
     */
    protected $context;

    /**
     * @var MockObject|RoleInterface
     */
    protected $orgRole;

    /**
     * @var MockObject|TokenInterface
     */
    protected $token;

    protected ?ExpressionVariableStorage $variableStorage = null;

    /**
     * @var
     */
    protected ?ExpressionVoter $voter = null;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
        $this->expressionLanguage = $this->getMockBuilder(ExpressionLanguage::class)->disableOriginalConstructor()->getMock();
        $this->trustResolver = $this->getMockBuilder(AuthenticationTrustResolverInterface::class)->getMock();
        $this->sidManager = $this->getMockBuilder(SecurityIdentityManagerInterface::class)->getMock();
        $this->context = $this->getMockBuilder(OrganizationalContextInterface::class)->getMock();
        $this->orgRole = $this->getMockBuilder(RoleInterface::class)->getMock();
        $this->token = $this->getMockBuilder(MockToken::class)->getMock();

        $this->variableStorage = new ExpressionVariableStorage(
            [
                'organizational_context' => $this->context,
                'organizational_role' => $this->orgRole,
            ],
            $this->sidManager
        );
        $this->variableStorage->add('trust_resolver', $this->trustResolver);

        $this->dispatcher->addSubscriber($this->variableStorage);

        $this->voter = new ExpressionVoter(
            $this->dispatcher,
            $this->expressionLanguage
        );
    }

    public function testAddExpressionLanguageProvider(): void
    {
        /** @var ExpressionFunctionProviderInterface $provider */
        $provider = $this->getMockBuilder(ExpressionFunctionProviderInterface::class)->getMock();

        $this->expressionLanguage->expects(static::once())
            ->method('registerProvider')
            ->with($provider)
        ;

        $this->voter->addExpressionLanguageProvider($provider);
    }

    public function testWithoutExpression(): void
    {
        $res = $this->voter->vote($this->token, null, [42]);

        static::assertSame(VoterInterface::ACCESS_ABSTAIN, $res);
    }

    public function getExpressionResults(): array
    {
        return [
            [VoterInterface::ACCESS_GRANTED, true],
            [VoterInterface::ACCESS_DENIED, false],
        ];
    }

    /**
     * @dataProvider getExpressionResults
     *
     * @param int  $resultVoter      The result of voter
     * @param bool $resultExpression The result of expression
     */
    public function testWithExpression(int $resultVoter, bool $resultExpression): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
            new RoleSecurityIdentity('role', AuthenticatedVoter::IS_AUTHENTICATED_FULLY),
        ];

        $this->sidManager->expects(static::once())
            ->method('getSecurityIdentities')
            ->with($this->token)
            ->willReturn($sids)
        ;

        $this->expressionLanguage->expects(static::once())
            ->method('evaluate')
            ->willReturnCallback(function ($attribute, array $variables) use ($resultExpression) {
                $this->assertInstanceOf(Expression::class, $attribute);
                $this->assertCount(8, $variables);
                $this->assertArrayHasKey('token', $variables);
                $this->assertArrayHasKey('user', $variables);
                $this->assertArrayHasKey('object', $variables);
                $this->assertArrayHasKey('subject', $variables);
                $this->assertArrayHasKey('roles', $variables);
                $this->assertArrayHasKey('trust_resolver', $variables);
                $this->assertArrayHasKey('organizational_context', $variables);
                $this->assertArrayHasKey('organizational_role', $variables);
                $this->assertArrayNotHasKey('request', $variables);

                $this->assertEquals(['ROLE_USER'], $variables['roles']);

                return $resultExpression;
            })
        ;

        $expression = new Expression('"ROLE_USER" in roles');
        $res = $this->voter->vote($this->token, null, [$expression]);

        static::assertSame($resultVoter, $res);
    }

    public function testWithoutSecurityIdentityManagerButWithRequestSubject(): void
    {
        $this->token->expects(static::once())
            ->method('getRoleNames')
            ->willReturn(['ROLE_USER'])
        ;

        $this->expressionLanguage->expects(static::once())
            ->method('evaluate')
            ->willReturnCallback(function ($attribute, array $variables) {
                $this->assertInstanceOf(Expression::class, $attribute);
                $this->assertCount(7, $variables);
                $this->assertArrayHasKey('token', $variables);
                $this->assertArrayHasKey('user', $variables);
                $this->assertArrayHasKey('object', $variables);
                $this->assertArrayHasKey('subject', $variables);
                $this->assertArrayHasKey('roles', $variables);
                $this->assertArrayHasKey('trust_resolver', $variables);
                $this->assertArrayNotHasKey('organizational_context', $variables);
                $this->assertArrayNotHasKey('organizational_role', $variables);
                $this->assertArrayHasKey('request', $variables);

                $this->assertEquals(['ROLE_USER'], $variables['roles']);

                return true;
            })
        ;

        $variableStorage = new ExpressionVariableStorage();
        $variableStorage->add('trust_resolver', $this->trustResolver);
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($variableStorage);

        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $expression = new Expression('"ROLE_USER" in roles');
        $voter = new ExpressionVoter(
            $dispatcher,
            $this->expressionLanguage
        );
        $res = $voter->vote($this->token, $request, [$expression]);

        static::assertSame(VoterInterface::ACCESS_GRANTED, $res);
    }
}
