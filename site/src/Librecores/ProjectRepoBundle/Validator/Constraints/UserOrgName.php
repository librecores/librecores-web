<?php
namespace Librecores\ProjectRepoBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint for user or organization names
 *
 * The two types of names live in the same namespace and follow the same rules.
 *
 * @Annotation
 */
class UserOrgName extends Constraint
{
    public $messageTooShort = 'The user/organization name must be '.
        'at least %minlength% characters long.';
    public $messageTooLong = 'The user/organization name must be '.
        'not more than %maxlength% characters long.';
    public $messageReservedName = 'The chosen user/organization name is '.
        'reserved for internal use.';
    public $messageUniqueName = 'The user/organization name is already taken.';
    public $messageInvalidCharacters = 'The user/organization name contains '.
        'invalid characters: it must start with a character, and be '.
        'followed by characters, numbers or the dash (-).';
}
