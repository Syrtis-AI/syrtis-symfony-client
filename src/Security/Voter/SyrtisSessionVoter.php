<?php

namespace SyrtisSymfonyClient\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use SyrtisSymfonyClient\Entity\SyrtisSession;
use SyrtisSymfonyClient\Entity\Traits\Manipulator\SyrtisSessionEntityManipulatorTrait;
use Wexample\SymfonyHelpers\Voter\AbstractEntityVoter;

/**
 * A conversation with Syrtis is its account's: read, written in and followed
 * live by it alone.
 */
class SyrtisSessionVoter extends AbstractEntityVoter
{
    use SyrtisSessionEntityManipulatorTrait;

    /**
     * @param SyrtisSession $subject
     */
    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool {
        $user = $token->getUser();

        return in_array($attribute, [self::VIEW, self::EDIT], true)
            && $user !== null
            && $subject->getUser()->getId()->equals($user->getId());
    }
}
