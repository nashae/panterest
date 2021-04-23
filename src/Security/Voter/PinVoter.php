<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PinVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['PIN_EDIT', 'PIN_DELETE'])
            && $subject instanceof \App\Entity\Pin;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User */
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case 'PIN_CREATE':
                return $user->isVerified();
                break;
            case 'PIN_EDIT':
                return $user->isVerified() && $user === $subject->getUser();
                break;
            case 'PIN_DELETE':
                return $user->isVerified() && $user === $subject->getUser();
                break;
        }
        return false;
    }
}
