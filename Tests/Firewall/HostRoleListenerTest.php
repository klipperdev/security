<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Tests\Firewall;

use Klipper\Component\Security\Firewall\HostRoleListener;
use Klipper\Component\Security\Identity\SecurityIdentityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class HostRoleListenerTest extends TestCase
{
    /**
     * @var MockObject|SecurityIdentityManagerInterface
     */
    protected $sidManager;

    protected array $config = [];

    /**
     * @var MockObject|Request
     */
    protected $request;

    /**
     * @var MockObject|RequestEvent
     */
    protected $event;

    /**
     * @var
     */
    protected ?HostRoleListener $listener = null;

    protected function setUp(): void
    {
        $this->sidManager = $this->getMockBuilder(SecurityIdentityManagerInterface::class)->getMock();
        $this->config = [
            '/foo.bar.tld/' => 'ROLE_HOST',
            '/.*.baz.tld/' => 'ROLE_HOST_BAZ',
            '/.*.foo.*/' => 'ROLE_HOST_FOO',
            '*.bar' => 'ROLE_HOST_BAR',
        ];
        $this->request = $this->getMockBuilder(Request::class)->getMock();
        $this->event = $this->getMockBuilder(RequestEvent::class)->disableOriginalConstructor()->getMock();
        $this->event->expects(static::any())
            ->method('getRequest')
            ->willReturn($this->request)
        ;

        $this->listener = new HostRoleListener($this->sidManager, $this->config);
    }

    public function testBasic(): void
    {
        static::assertTrue($this->listener->isEnabled());
        $this->listener->setEnabled(false);
        static::assertFalse($this->listener->isEnabled());
    }

    public function testInvokeWithDisabledListener(): void
    {
        $this->sidManager->expects(static::never())
            ->method('addSpecialRole')
        ;

        $this->listener->setEnabled(false);
        ($this->listener)($this->event);
    }

    public function testInvokeWithoutHostRole(): void
    {
        $this->request->expects(static::once())
            ->method('getHttpHost')
            ->willReturn('no.host-role.tld')
        ;

        $this->sidManager->expects(static::never())
            ->method('addSpecialRole')
        ;

        ($this->listener)($this->event);
    }

    public function testWithoutToken(): void
    {
        $this->request->expects(static::once())
            ->method('getHttpHost')
            ->willReturn('foo.bar.tld')
        ;

        $this->sidManager->expects(static::once())
            ->method('addSpecialRole')
            ->with('ROLE_HOST')
        ;

        ($this->listener)($this->event);
    }

    public function testWithAlreadyRoleIncluded(): void
    {
        $token = new AnonymousToken('secret', 'user', [
            'ROLE_HOST',
        ]);

        $this->request->expects(static::once())
            ->method('getHttpHost')
            ->willReturn('foo.bar.tld')
        ;

        $this->sidManager->expects(static::once())
            ->method('addSpecialRole')
            ->with('ROLE_HOST')
        ;

        ($this->listener)($this->event);

        static::assertCount(1, $token->getRoleNames());
    }

    public function getHosts(): array
    {
        return [
            ['foo.bar.tld', 'ROLE_HOST'],
            ['foo.baz.tld', 'ROLE_HOST_BAZ'],
            ['a.foo.tld', 'ROLE_HOST_FOO'],
            ['b.foo.tld', 'ROLE_HOST_FOO'],
            ['a.foo.com', 'ROLE_HOST_FOO'],
            ['b.foo.com', 'ROLE_HOST_FOO'],
            ['a.foo.org', 'ROLE_HOST_FOO'],
            ['b.foo.org', 'ROLE_HOST_FOO'],
            ['www.example.bar', 'ROLE_HOST_BAR'],
        ];
    }

    /**
     * @dataProvider getHosts
     *
     * @param string $host      The host name
     * @param string $validRole The valid role
     */
    public function testInvoke($host, $validRole): void
    {
        $token = new AnonymousToken('secret', 'user', [
            'ROLE_FOO',
        ]);

        $this->request->expects(static::once())
            ->method('getHttpHost')
            ->willReturn($host)
        ;

        $this->sidManager->expects(static::once())
            ->method('addSpecialRole')
            ->with($validRole)
        ;

        ($this->listener)($this->event);

        static::assertCount(1, $token->getRoleNames());
    }
}
