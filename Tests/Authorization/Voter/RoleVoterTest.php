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

use Klipper\Component\Security\Authorization\Voter\RoleVoter;
use Klipper\Component\Security\Identity\RoleSecurityIdentity;
use Klipper\Component\Security\Identity\SecurityIdentityManagerInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockObject;
use Klipper\Component\Security\Tests\Fixtures\Model\MockRole;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class RoleVoterTest extends TestCase
{
    /**
     * @var MockObject|SecurityIdentityManagerInterface
     */
    protected $sidManager;

    protected ?RoleVoter $voter = null;

    protected function setUp(): void
    {
        $this->sidManager = $this->getMockBuilder(SecurityIdentityManagerInterface::class)->getMock();
        $this->voter = new RoleVoter($this->sidManager, 'TEST_');
    }

    public function getAccessResults(): array
    {
        return [
            [['TEST_ADMIN'], VoterInterface::ACCESS_GRANTED],
            [['TEST_USER'], VoterInterface::ACCESS_DENIED],
            [['ROLE_ADMIN'], VoterInterface::ACCESS_ABSTAIN],
        ];
    }

    /**
     * @dataProvider getAccessResults
     *
     * @param string[] $attributes The voter attributes
     * @param int      $access     The access status of voter
     */
    public function testExtractRolesWithAccessGranted(array $attributes, int $access): void
    {
        /** @var MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'TEST_ADMIN'),
        ];

        $this->sidManager->expects(static::atLeast(2))
            ->method('getSecurityIdentities')
            ->willReturn($sids)
        ;

        static::assertSame($access, $this->voter->vote($token, null, $attributes));
        static::assertSame($access, $this->voter->vote($token, null, $attributes));
    }
}
