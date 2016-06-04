<?php
namespace Librecores\ProjectRepoBundle\Security;

use Librecores\ProjectRepoBundle\Entity\Project;
use Librecores\ProjectRepoBundle\Entity\User;
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
     */
    private function canEdit(Project $project, User $user)
    {
        // XXX: Projects owned by an organization are not supported for now
        if ($project->getParentOrganization() !== null) {
            return false;
        }
        return $user === $project->getParentUser();
    }
}
