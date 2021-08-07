<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Identity;

use Klipper\Component\Security\Event\AddSecurityIdentityEvent;
use Klipper\Component\Security\Event\PostSecurityIdentityEvent;
use Klipper\Component\Security\Event\PreSecurityIdentityEvent;
use Klipper\Component\Security\Identity\SecurityIdentityInterface;
use Klipper\Component\Security\Identity\SecurityIdentityManager;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\Security\Tests\Fixtures\Token\MockToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SecurityIdentityManagerTest extends TestCase
{
    protected ?EventDispatcher $dispatcher = null;

    /**
     * @var MockObject|RoleHierarchyInterface
     */
    protected $roleHierarchy;

    /**
     * @var AuthenticationTrustResolverInterface|MockObject
     */
    protected $authenticationTrustResolver;

    /**
     * @var
     */
    protected ?SecurityIdentityManager $sidManager = null;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
        $this->roleHierarchy = $this->getMockBuilder(RoleHierarchy::class)->disableOriginalConstructor()->getMock();
        $this->authenticationTrustResolver = $this->getMockBuilder(AuthenticationTrustResolverInterface::class)->getMock();

        $this->sidManager = new SecurityIdentityManager(
            $this->dispatcher,
            $this->roleHierarchy,
            $this->authenticationTrustResolver
        );
    }

    public function getAuthenticationTrustResolverStatus(): array
    {
        return [
            ['isFullFledged', 7],
            ['isRememberMe', 6],
            ['isAnonymous', 5],
        ];
    }

    /**
     * @dataProvider getAuthenticationTrustResolverStatus
     *
     * @param string $trustMethod  The method for the authentication trust resolver
     * @param int    $sidFinalSize The final size of security identities list
     */
    public function testGetSecurityIdentities(string $trustMethod, int $sidFinalSize): void
    {
        $preEventAction = false;
        $addEventAction = false;
        $postEventAction = false;

        $customSid = $this->getMockBuilder(SecurityIdentityInterface::class)->getMock();

        $this->dispatcher->addListener(PreSecurityIdentityEvent::class, function (PreSecurityIdentityEvent $event) use (&$preEventAction): void {
            $preEventAction = true;
            $this->assertCount(0, $event->getSecurityIdentities());
        });

        $this->dispatcher->addListener(AddSecurityIdentityEvent::class, function (AddSecurityIdentityEvent $event) use (&$addEventAction, $customSid): void {
            $addEventAction = true;
            $this->assertCount(2, $event->getSecurityIdentities());

            $sids = $event->getSecurityIdentities();
            $sids[] = $customSid;
            $event->setSecurityIdentities($sids);

            $this->assertCount(3, $event->getSecurityIdentities());
        });

        $this->dispatcher->addListener(PostSecurityIdentityEvent::class, function (PostSecurityIdentityEvent $event) use (&$postEventAction, $sidFinalSize): void {
            $postEventAction = true;
            $this->assertCount($sidFinalSize, $event->getSecurityIdentities());
        });

        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $user->expects(static::once())
            ->method('getUserIdentifier')
            ->willReturn('user.test')
        ;

        $tokenRoles = [
            'ROLE_TOKEN',
        ];
        $token = new MockToken($tokenRoles);
        $token->setUser($user);

        $this->roleHierarchy->expects(static::once())
            ->method('getReachableRoleNames')
            ->with($tokenRoles)
            ->willReturn($tokenRoles)
        ;

        if (\in_array($trustMethod, ['isRememberMe', 'isAnonymous'], true)) {
            $this->authenticationTrustResolver->expects(static::once())
                ->method('isFullFledged')
                ->with($token)
                ->willReturn(false)
            ;
        }

        if ('isAnonymous' === $trustMethod) {
            $this->authenticationTrustResolver->expects(static::once())
                ->method('isRememberMe')
                ->with($token)
                ->willReturn(false)
            ;
        }

        $this->authenticationTrustResolver->expects(static::once())
            ->method($trustMethod)
            ->with($token)
            ->willReturn(true)
        ;

        $this->sidManager->addSpecialRole('ROLE_BAZ');

        $this->sidManager->getSecurityIdentities($token);

        static::assertTrue($preEventAction);
        static::assertTrue($addEventAction);
        static::assertTrue($postEventAction);
    }

    public function testGetSecurityIdentitiesWithoutToken(): void
    {
        $this->roleHierarchy->expects(static::never())
            ->method('getReachableRoleNames')
        ;

        $sids = $this->sidManager->getSecurityIdentities();

        static::assertCount(0, $sids);
    }
}
