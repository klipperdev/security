<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Authorization\Voter;

use Klipper\Component\Security\Identity\SecurityIdentityManagerInterface;
use Klipper\Component\Security\Permission\FieldVote;
use Klipper\Component\Security\Permission\PermissionManagerInterface;
use Klipper\Component\Security\Permission\PermVote;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Permission voter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PermissionVoter implements VoterInterface
{
    private PermissionManagerInterface $permissionManager;

    private SecurityIdentityManagerInterface $sim;

    private bool $allowNotManagedSubject;

    /**
     * @param PermissionManagerInterface       $permissionManager      The permission manager
     * @param SecurityIdentityManagerInterface $sim                    The security identity manager
     * @param bool                             $allowNotManagedSubject Check if the voter allow the not managed subject
     */
    public function __construct(
        PermissionManagerInterface $permissionManager,
        SecurityIdentityManagerInterface $sim,
        bool $allowNotManagedSubject = true
    ) {
        $this->permissionManager = $permissionManager;
        $this->sim = $sim;
        $this->allowNotManagedSubject = $allowNotManagedSubject;
    }

    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        $vote = self::ACCESS_ABSTAIN;

        foreach ($attributes as $attribute) {
            if (!$this->supports($attribute, $subject)) {
                continue;
            }

            $vote = self::ACCESS_DENIED;

            if ($this->voteOnAttribute($attribute, $subject, $token)) {
                return self::ACCESS_GRANTED;
            }
        }

        return $vote;
    }

    /**
     * @param mixed $attribute
     * @param mixed $subject
     */
    protected function supports($attribute, $subject): bool
    {
        return $this->isAttributeSupported($attribute)
            && $this->isSubjectSupported($subject)
            && $this->isSubjectManaged($subject);
    }

    /**
     * Check if the attribute is supported.
     *
     * @param mixed $attribute The attribute
     */
    protected function isAttributeSupported($attribute): bool
    {
        return $attribute instanceof PermVote
            || (\is_string($attribute) && 0 === stripos(strtolower($attribute), 'perm:'));
    }

    /**
     * Check if the subject is supported.
     *
     * @param null|FieldVote|mixed $subject The subject
     */
    protected function isSubjectSupported($subject): bool
    {
        if (null === $subject || \is_string($subject) || $subject instanceof FieldVote || \is_object($subject)) {
            return true;
        }

        return \is_array($subject)
            && isset($subject[0], $subject[1])
            && (\is_string($subject[0]) || \is_object($subject[0]))
            && \is_string($subject[1]);
    }

    /**
     * Check if the subject is managed.
     *
     * @param null|FieldVote|mixed $subject The subject
     */
    protected function isSubjectManaged($subject): bool
    {
        return null === $subject
            || $this->allowNotManagedSubject
            || $this->permissionManager->isManaged($this->convertSubject($subject));
    }

    /**
     * @param PermVote|string      $attribute The attribute
     * @param null|FieldVote|mixed $subject   The subject
     * @param TokenInterface       $token     The security token
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $sids = $this->sim->getSecurityIdentities($token);
        $attribute = $attribute instanceof PermVote ? $attribute->getPermission() : substr($attribute, 5);
        $subject = $this->convertSubject($subject);

        return !$this->permissionManager->isEnabled()
            || $this->permissionManager->isGranted($sids, $attribute, $subject);
    }

    /**
     * @param null|FieldVote|mixed $subject The subject
     *
     * @return FieldVote|object|string
     */
    protected function convertSubject($subject)
    {
        if (\is_array($subject) && isset($subject[0], $subject[1])) {
            $subject = new FieldVote($subject[0], $subject[1]);
        }

        return $subject;
    }
}
