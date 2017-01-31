<?php
namespace Librecores\ProjectRepoBundle\Security;

use Librecores\ProjectRepoBundle\Entity\Project;
use Librecores\ProjectRepoBundle\Entity\User;
use Librecores\ProjectRepoBundle\Entity\OrganizationMember;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Authorization voter to determine access to a given Project object
 *
 * We use the following access policy:
 * - VIEW is allowed for anyone
 * - EDIT is only allowed for the project owner
 */
class ProjectVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::EDIT))) {
            return false;
        }

        if (!$subject instanceof Project) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        // viewing is always allowed
        if ($attribute == self::VIEW) {
            return true;
        }

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        /** @var Project $project */
        $project = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($project, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * Can a given user edit the project?
     *
     * @param Project $project
     * @param User $user
     * @return boolean
     */
    private function canEdit(Project $project, User $user)
    {
        // Check parent user

        $userResult = false;

        $parentUser = $project->getParentUser();

        if ($parentUser !== null) {
            $userResult = ($user === $parentUser);
        }

        // Check parent organization

        $orgResult = false;

        $parentOrganization = $project->getParentOrganization();

        if ($parentOrganization !== null) {
            foreach ($parentOrganization->getMembers() as $m) {
                if (($m->getUser()        === $user) &&
                    ($m->getPermission() === OrganizationMember::PERMISSION_MEMBER ||
                     $m->getPermission() === OrganizationMember::PERMISSION_ADMIN)) {
                    $orgResult = true;
                    break;
                }
            }
        }

        return $orgResult or $userResult;
    }
}
