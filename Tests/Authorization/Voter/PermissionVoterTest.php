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

use Klipper\Component\Security\Authorization\Voter\PermissionVoter;
use Klipper\Component\Security\Identity\RoleSecurityIdentity;
use Klipper\Component\Security\Identity\SecurityIdentityManagerInterface;
use Klipper\Component\Security\Permission\FieldVote;
use Klipper\Component\Security\Permission\PermissionManagerInterface;
use Klipper\Component\Security\Permission\PermVote;
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
final class PermissionVoterTest extends TestCase
{
    /**
     * @var PermissionManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $permManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|SecurityIdentityManagerInterface
     */
    protected $sidManager;

    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * @var PermissionVoter
     */
    protected $voter;

    protected function setUp(): void
    {
        $this->permManager = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
        $this->sidManager = $this->getMockBuilder(SecurityIdentityManagerInterface::class)->getMock();
        $this->token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $this->permManager->expects(static::any())
            ->method('isEnabled')
            ->willReturn(true)
        ;

        $this->voter = new PermissionVoter(
            $this->permManager,
            $this->sidManager
        );
    }

    public function getVoteAttributes(): array
    {
        $class = MockObject::class;
        $object = new MockObject('foo');
        $permVote = new PermVote('view');
        $fieldVote = new FieldVote($object, 'name');
        $arrayValid = [$object, 'name'];
        $arrayInvalid = [$object];

        return [
            [[42], $class, VoterInterface::ACCESS_ABSTAIN, null, true, false],
            [[42], $class, VoterInterface::ACCESS_ABSTAIN, null, true, true],

            [[42], $object, VoterInterface::ACCESS_ABSTAIN, null, true, false],
            [[42], $object, VoterInterface::ACCESS_ABSTAIN, null, true, true],

            [[42], $fieldVote, VoterInterface::ACCESS_ABSTAIN, null, true, false],
            [[42], $fieldVote, VoterInterface::ACCESS_ABSTAIN, null, true, true],

            [[42], $arrayValid, VoterInterface::ACCESS_ABSTAIN, null, true, false],
            [[42], $arrayValid, VoterInterface::ACCESS_ABSTAIN, null, true, true],

            [[42], $arrayInvalid, VoterInterface::ACCESS_ABSTAIN, null, true, false],
            [[42], $arrayInvalid, VoterInterface::ACCESS_ABSTAIN, null, true, true],

            [['view'], $class, VoterInterface::ACCESS_ABSTAIN, null, true, false],
            [['view'], $class, VoterInterface::ACCESS_ABSTAIN, null, true, true],

            [['view'], $object, VoterInterface::ACCESS_ABSTAIN, null, true, false],
            [['view'], $object, VoterInterface::ACCESS_ABSTAIN, null, true, true],

            [['view'], $fieldVote, VoterInterface::ACCESS_ABSTAIN, null, true, false],
            [['view'], $fieldVote, VoterInterface::ACCESS_ABSTAIN, null, true, true],

            [['view'], $arrayValid, VoterInterface::ACCESS_ABSTAIN, null, true, false],
            [['view'], $arrayValid, VoterInterface::ACCESS_ABSTAIN, null, true, true],

            [['view'], $arrayInvalid, VoterInterface::ACCESS_ABSTAIN, null, true, false],
            [['view'], $arrayInvalid, VoterInterface::ACCESS_ABSTAIN, null, true, true],

            [['perm:view'], $class, VoterInterface::ACCESS_GRANTED, true, true, false],
            [['perm:view'], $class, VoterInterface::ACCESS_GRANTED, true, false, true],
            [[$permVote], $class, VoterInterface::ACCESS_GRANTED, true, true, false],
            [[$permVote], $class, VoterInterface::ACCESS_GRANTED, true, false, true],

            [['perm:view'], $object, VoterInterface::ACCESS_GRANTED, true, true, false],
            [['perm:view'], $object, VoterInterface::ACCESS_GRANTED, true, false, true],
            [[$permVote], $object, VoterInterface::ACCESS_GRANTED, true, true, false],
            [[$permVote], $object, VoterInterface::ACCESS_GRANTED, true, false, true],

            [['perm:view'], $object, VoterInterface::ACCESS_DENIED, false, true, false],
            [['perm:view'], $object, VoterInterface::ACCESS_DENIED, false, false, true],
            [[$permVote], $object, VoterInterface::ACCESS_DENIED, false, true, false],
            [[$permVote], $object, VoterInterface::ACCESS_DENIED, false, false, true],

            [['perm:view'], $fieldVote, VoterInterface::ACCESS_GRANTED, true, true, false],
            [['perm:view'], $fieldVote, VoterInterface::ACCESS_GRANTED, true, false, true],
            [[$permVote], $fieldVote, VoterInterface::ACCESS_GRANTED, true, true, false],
            [[$permVote], $fieldVote, VoterInterface::ACCESS_GRANTED, true, false, true],

            [['perm:view'], $fieldVote, VoterInterface::ACCESS_DENIED, false, true, false],
            [['perm:view'], $fieldVote, VoterInterface::ACCESS_DENIED, false, false, true],
            [[$permVote], $fieldVote, VoterInterface::ACCESS_DENIED, false, true, false],
            [[$permVote], $fieldVote, VoterInterface::ACCESS_DENIED, false, false, true],

            [['perm:view'], $arrayValid, VoterInterface::ACCESS_GRANTED, true, true, false],
            [['perm:view'], $arrayValid, VoterInterface::ACCESS_GRANTED, true, false, true],
            [[$permVote], $arrayValid, VoterInterface::ACCESS_GRANTED, true, true, false],
            [[$permVote], $arrayValid, VoterInterface::ACCESS_GRANTED, true, false, true],

            [['perm:view'], $arrayValid, VoterInterface::ACCESS_DENIED, false, true, false],
            [['perm:view'], $arrayValid, VoterInterface::ACCESS_DENIED, false, false, true],
            [[$permVote], $arrayValid, VoterInterface::ACCESS_DENIED, false, true, false],
            [[$permVote], $arrayValid, VoterInterface::ACCESS_DENIED, false, false, true],

            [['perm:view'], $arrayInvalid, VoterInterface::ACCESS_ABSTAIN, null, true, false],
            [['perm:view'], $arrayInvalid, VoterInterface::ACCESS_ABSTAIN, null, true, true],
            [[$permVote], $arrayInvalid, VoterInterface::ACCESS_ABSTAIN, null, true, false],
            [[$permVote], $arrayInvalid, VoterInterface::ACCESS_ABSTAIN, null, true, true],

            [['foo'], null, VoterInterface::ACCESS_ABSTAIN, null, true, false],
            [['foo'], null, VoterInterface::ACCESS_ABSTAIN, null, true, true],

            [['perm:foo'], null, VoterInterface::ACCESS_GRANTED, true, true, false],
            [['perm:foo'], null, VoterInterface::ACCESS_GRANTED, true, true, true],

            [['perm:foo'], null, VoterInterface::ACCESS_DENIED, false, true, false],
            [['perm:foo'], null, VoterInterface::ACCESS_DENIED, false, true, true],
        ];
    }

    /**
     * @dataProvider getVoteAttributes
     *
     * @param array     $attributes             The attributes
     * @param mixed     $subject                The subject
     * @param int       $result                 The expected result
     * @param null|bool $permManagerResult      The result of permission manager
     * @param bool      $isManaged              Check if the subject is managed
     * @param bool      $allowNotManagedSubject Allow the not managed subject
     */
    public function testVote(array $attributes, $subject, int $result, ?bool $permManagerResult, bool $isManaged, bool $allowNotManagedSubject): void
    {
        $this->voter = new PermissionVoter($this->permManager, $this->sidManager, $allowNotManagedSubject);

        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];

        if (null !== $permManagerResult) {
            $this->sidManager->expects(static::once())
                ->method('getSecurityIdentities')
                ->with($this->token)
                ->willReturn($sids)
            ;

            if (\is_array($subject) && isset($subject[0], $subject[1])) {
                $expectedSubject = new FieldVote($subject[0], $subject[1]);

                if ($allowNotManagedSubject) {
                    $this->permManager->expects(static::never())->method('isManaged');
                } elseif (null !== $subject) {
                    $this->permManager->expects(static::once())
                        ->method('isManaged')
                        ->with($expectedSubject)
                        ->willReturn($isManaged)
                    ;
                }

                $this->permManager->expects(static::once())
                    ->method('isGranted')
                    ->with($sids, $attributes[0] instanceof PermVote ? $attributes[0]->getPermission() : substr($attributes[0], 5), $expectedSubject)
                    ->willReturn($permManagerResult)
                ;
            } else {
                if ($allowNotManagedSubject) {
                    $this->permManager->expects(static::never())->method('isManaged');
                } elseif (null !== $subject) {
                    $this->permManager->expects(static::once())
                        ->method('isManaged')
                        ->with($subject)
                        ->willReturn($isManaged)
                    ;
                }

                $this->permManager->expects(static::once())
                    ->method('isGranted')
                    ->with($sids, $attributes[0] instanceof PermVote ? $attributes[0]->getPermission() : substr($attributes[0], 5), $subject)
                    ->willReturn($permManagerResult)
                ;
            }
        }

        static::assertSame($result, $this->voter->vote($this->token, $subject, $attributes));
    }
}
