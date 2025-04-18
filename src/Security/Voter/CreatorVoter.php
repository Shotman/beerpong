<?php

namespace App\Security\Voter;

use App\Entity\{Championship, Tournament};
use Symfony\Component\Security\Core\{
    Authentication\Token\TokenInterface,
    Authorization\Voter\Voter,
    User\UserInterface
};

class CreatorVoter extends Voter
{
    public const string EDIT = 'EDIT_CHAMPIONSHIP_TOURNAMENT';
    public const string VIEW = 'VIEW_CHAMPIONSHIP_TOURNAMENT';
    public const string DELETE = 'DELETE_CHAMPIONSHIP_TOURNAMENT';
    public const string CREATE = 'CREATE_CHAMPIONSHIP_TOURNAMENT';
    public const string LIST_ALL = 'LIST_ALL_CHAMPIONSHIP_TOURNAMENT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return $attribute == self::CREATE || (in_array($attribute, [self::EDIT, self::VIEW,self::DELETE, self::LIST_ALL])
            && ($subject instanceof Tournament || $subject instanceof Championship));
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        $response = false;
        // if the user is anonymous, do not grant access
        if(!is_null($user) && in_array("ROLE_SUPER_ADMIN",$user->getRoles()))
            return true;
        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::DELETE:
            case self::EDIT:
                $response = !is_null($user) && $this->isRightUserForSubject($user,$subject);
            break;
            case self::VIEW:
                $response = $subject->isPublic() || (!is_null($user) && $this->isRightUserForSubject($user,$subject));
                break;
            case self::CREATE:
                $response = !is_null($user) && in_array("ROLE_ADMIN",$user->getRoles());
                break;
            case self::LIST_ALL:
                $response = !is_null($user) && in_array("ROLE_SUPER_ADMIN",$user->getRoles());
                break;
        }
        return $response;
    }

    private function isRightUserForSubject(UserInterface $user,Tournament|Championship $subject): bool
    {
        return in_array("ROLE_SUPER_ADMIN",$user->getRoles()) || ($user->getUserIdentifier() === $subject->getAdmin()->getUserIdentifier() && in_array("ROLE_ADMIN",$user->getRoles()));
    }
}
