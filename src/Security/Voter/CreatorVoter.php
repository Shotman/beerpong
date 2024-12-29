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
    public const EDIT = "EDIT_CHAMPIONSHIP_TOURNAMENT";
    public const VIEW = "VIEW_CHAMPIONSHIP_TOURNAMENT";
    public const DELETE = "DELETE_CHAMPIONSHIP_TOURNAMENT";
    public const CREATE = "CREATE_CHAMPIONSHIP_TOURNAMENT";
    public const LIST_ALL = "LIST_ALL_CHAMPIONSHIP_TOURNAMENT";

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::CREATE]) ||
            (in_array($attribute, [
                self::EDIT,
                self::VIEW,
                self::DELETE,
                self::LIST_ALL,
            ]) &&
                ($subject instanceof Tournament ||
                    $subject instanceof Championship));
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool {
        $user = $token->getUser();
        $response = false;
        // if the user is anonymous, do not grant access
        // if (!$user instanceof UserInterface) {
        //     return $response;
        // }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                $response = $this->isRightUserForSubject($user, $subject);
                break;
            case self::VIEW:
                $response =
                    $subject->isPublic() ||
                    $this->isRightUserForSubject($user, $subject);
                break;
            case self::CREATE:
                if (!$user instanceof UserInterface) {
                    return false;
                }
                $response =
                    in_array("ROLE_ADMIN", $user->getRoles()) ||
                    in_array("ROLE_SUPER_ADMIN", $user->getRoles());
                break;
            case self::DELETE:
                $response = $this->isRightUserForSubject($user, $subject);
                break;
            case self::LIST_ALL:
                if (!$user instanceof UserInterface) {
                    return false;
                }
                $response = in_array("ROLE_SUPER_ADMIN", $user->getRoles());
                break;
        }
        return $response;
    }

    private function isRightUserForSubject(
        ?UserInterface $user,
        Tournament|Championship $subject
    ): bool {
        if (!$user instanceof UserInterface) {
            return false;
        }
        return in_array("ROLE_SUPER_ADMIN", $user->getRoles()) ||
            ($user->getUserIdentifier() ===
                $subject->getAdmin()->getUserIdentifier() &&
                in_array("ROLE_ADMIN", $user->getRoles()));
    }
}
