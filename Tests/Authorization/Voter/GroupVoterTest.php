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

use Klipper\Component\Security\Authorization\Voter\GroupVoter;
use Klipper\Component\Security\Identity\GroupSecurityIdentity;
use Klipper\Component\Security\Identity\SecurityIdentityManagerInterface;
use Klipper\Component\Security\Tests\Fixtures\Model\MockGroup;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class GroupVoterTest extends TestCase
{
    /**
     * @var MockObject|SecurityIdentityManagerInterface
     */
    protected $sidManager;

    /**
     * @var GroupVoter
     */
    protected $voter;

    protected function setUp(): void
    {
        $this->sidManager = $this->getMockBuilder(SecurityIdentityManagerInterface::class)->getMock();
        $this->voter = new GroupVoter($this->sidManager, null);
    }

    public function getAccessResults(): array
    {
        return [
            [['GROUP_FOO'], VoterInterface::ACCESS_GRANTED],
            [['GROUP_BAR'], VoterInterface::ACCESS_DENIED],
            [['TEST_FOO'], VoterInterface::ACCESS_ABSTAIN],
        ];
    }

    /**
     * @dataProvider getAccessResults
     *
     * @param string[] $attributes The voter attributes
     * @param int      $access     The access status of voter
     */
    public function testExtractRolesWithAccessGranted(array $attributes, $access): void
    {
        /** @var MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $sids = [
            new GroupSecurityIdentity(MockGroup::class, 'FOO'),
        ];

        if (VoterInterface::ACCESS_ABSTAIN !== $access) {
            $this->sidManager->expects(static::atLeast(2))
                ->method('getSecurityIdentities')
                ->willReturn($sids)
            ;
        }

        static::assertSame($access, $this->voter->vote($token, null, $attributes));
        static::assertSame($access, $this->voter->vote($token, null, $attributes));
    }
}
