<?php

namespace App\Validator\Constraints;

use Doctrine\ORM\NonUniqueResultException;
use App\Repository\OrganizationRepository;
use App\Repository\UserRepository;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Validate a user or organization name
 *
 * Validation encompasses both length rules of the name, as well as enforcing
 * the uniqueness of it. Some of the validation rules in this class might
 * duplicate other validation rules (i.e. the length validation), but we add
 * them here anyways to have *one* place which ensures that no user or org name
 * with invalid format can be chosen.
 */
class UserOrgNameValidator extends ConstraintValidator
{
    /**
     * Minimum length of the user or org name
     *
     * @var int
     */
    const LENGTH_MIN = 4;

    /**
     * Maximum length of the user or org name
     *
     * @var int
     */
    const LENGTH_MAX = 39;
    /**
     * Names reserved for internal use, mostly to avoid clashes where
     * a name is equal to a route.
     * To be on the safe side, also add names which would be considered
     * invalid by the length rules above (variables might change).
     *
     * @var string[]
     */
    const RESERVED_NAMES = [
        'org',
        'orgs',
        'planet',
        'project',
        'projects',
        'search',
        'static',
        'unassigned',
        'user',
        'admin',
        'administrator',
    ];

    /**
     * Routes that are excluded from checking for a match in userOrgReserved()
     * since they will always match regardless of the selected username and
     * thus produce false positive violations on any selected username.
     *
     * @var string[]
     */
    const EXCLUDE_ROUTE_CHECK = ['librecores_project_repo_user_org_view'];

    /**
     * Regular expression checking for valid characters in an user or org name
     *
     * The name is first converted to lowercase before passing to this regex,
     * and the length of the name is already checked.
     * When changing this regex also change the validation message inside the
     * UserOrgName class.
     *
     * @var string
     */
    const VALID_NAME_REGEX = '/^[a-z][a-z0-9-]+$/';

    /** @var UserRepository */
    private $userRepository;

    /**
     * @var OrganizationRepository
     */
    private $organizationRepository;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * UserOrgNameValidator constructor.
     *
     * @param RouterInterface        $router
     * @param UserRepository         $userRepository
     * @param OrganizationRepository $organizationRepository
     */
    public function __construct(
        RouterInterface $router,
        UserRepository $userRepository,
        OrganizationRepository $organizationRepository
    ) {
        $this->router = $router;
        $this->userRepository = $userRepository;
        $this->organizationRepository = $organizationRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        $value = strtolower($value);

        $type = 'none';
        if (isset($constraint->payload['type'])) {
            $type = $constraint->payload['type'];
        }

        if (strlen($value) < self::LENGTH_MIN) {
            $this->context->buildViolation($constraint->messageTooShort)
                ->setParameter('%string%', $value)
                ->setParameter('%minlength%', self::LENGTH_MIN)
                ->addViolation();
        }

        if (strlen($value) > self::LENGTH_MAX) {
            $this->context
                ->buildViolation($constraint->messageTooLong)
                ->setParameter('%string%', $value)
                ->setParameter('%maxlength%', self::LENGTH_MAX)
                ->addViolation();
        }

        if (!preg_match(self::VALID_NAME_REGEX, $value)) {
            $this->context
                ->buildViolation($constraint->messageInvalidCharacters)
                ->setParameter('%string%', $value)
                ->addViolation();
        }

        if ($this->userOrOrgNameExists($value, $type)) {
            $this->context
                ->buildViolation($constraint->messageUniqueName)
                ->setParameter('%string%', $value)
                ->addViolation();
        } elseif ($this->userOrOrgReserved($value)) {
            $this->context
                ->buildViolation($constraint->messageReservedName)
                ->setParameter('%string%', $value)
                ->addViolation();
        }
    }

    /**
     * Check if a username or organization name already exists on LibreCores
     *
     * @param string $name user or org name
     * @param string $type "user" or "org"
     *
     * @return bool
     *
     * @throws NonUniqueResultException
     */
    private function userOrOrgNameExists($name, $type)
    {
        $name = strtolower($name);

        // Check the org name against existing usernames
        if ($type === "org") {
            $cntUser = $this->userRepository->count(['usernameCanonical' => $name]);
            if ($cntUser !== 0) {
                return true;
            }
        }

        // Check the username against existing org names
        if ($type === "user") {
            $cntOrg = $this->organizationRepository->countByNameIgnoreCase($name);
            if ($cntOrg !== 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find out if a given top-level URL value is reserved
     *
     * @param string $value
     *
     * @return bool
     */
    private function userOrOrgReserved($value)
    {
        if (in_array($value, self::RESERVED_NAMES)) {
            return true;
        }

        /*
         * Also check for valid top-level routes in case we forgot to exclude
         * a route pattern in the RESERVED_NAMES array.
         */

        try {
            $route = $this->router->match('/'.$value)['_route'];
        } catch (ResourceNotFoundException $e) {
            $route = null;
        }

        return ($route !== null && !in_array($route, self::EXCLUDE_ROUTE_CHECK));
    }
}
