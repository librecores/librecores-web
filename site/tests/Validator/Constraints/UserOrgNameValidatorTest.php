<?php

namespace App\Validator\Constraints;

use App\Repository\OrganizationRepository;
use App\Repository\UserRepository;
use LibreCores\TestUtils\Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UserOrgNameValidatorTest extends TestCase
{
    public function testValidateRejectsANameLessThan4Characters()
    {
        $mockRouter = $this->createMock(Router::class);
        $mockUserRepository = $this->createMock(UserRepository::class);
        $mockOrgRepository = $this->createMock(OrganizationRepository::class);

        $validator = new UserOrgNameValidator($mockRouter, $mockUserRepository, $mockOrgRepository);

        $constraint = new UserOrgName();

        $name = Generator::randomString(2, 3);
        /** @var ExecutionContextInterface $mockExecutionContext */
        $mockExecutionContext = $this->createMockExecutionContext($constraint->messageTooShort);

        $validator->initialize($mockExecutionContext);
        $validator->validate($name, $constraint);
    }

    public function testValidateRejectsANameGreaterThan39Characters()
    {
        $mockRouter = $this->createMock(Router::class);
        $mockUserRepository = $this->createMock(UserRepository::class);
        $mockOrgRepository = $this->createMock(OrganizationRepository::class);

        $validator = new UserOrgNameValidator($mockRouter, $mockUserRepository, $mockOrgRepository);

        $constraint = new UserOrgName();

        $name = Generator::randomString(40, 255);

        /** @var ExecutionContextInterface $mockExecutionContext */
        $mockExecutionContext = $this->createMockExecutionContext($constraint->messageTooLong);

        $validator->initialize($mockExecutionContext);
        $validator->validate($name, $constraint);
    }

    public function testValidateRejectsANameWithInvalidCharacters()
    {
        $mockRouter = $this->createMock(Router::class);
        $mockUserRepository = $this->createMock(UserRepository::class);
        $mockOrgRepository = $this->createMock(OrganizationRepository::class);

        $validator = new UserOrgNameValidator($mockRouter, $mockUserRepository, $mockOrgRepository);

        $constraint = new UserOrgName();

        $name = '!nvalid$';

        /** @var ExecutionContextInterface $mockExecutionContext */
        $mockExecutionContext = $this->createMockExecutionContext($constraint->messageInvalidCharacters);

        $validator->initialize($mockExecutionContext);
        $validator->validate($name, $constraint);
    }

    public function testValidateRejectsAReservedName()
    {
        /** @var Router $mockRouter */
        $mockRouter = $this->createMock(Router::class);

        /** @var UserRepository $mockUserRepository */
        $mockUserRepository = $this->createMock(UserRepository::class);

        /** @var OrganizationRepository $mockOrgRepository */
        $mockOrgRepository = $this->createMock(OrganizationRepository::class);

        $validator = new UserOrgNameValidator(
            $mockRouter,
            $mockUserRepository,
            $mockOrgRepository
        );

        $constraint = new UserOrgName();

        $choices = array_values(
            array_filter(
                UserOrgNameValidator::RESERVED_NAMES,
                function ($s) {
                    return strlen($s) > 4;
                }
            )
        );
        $name = $choices[random_int(0, count($choices) - 1)];

        /** @var ExecutionContextInterface $mockExecutionContext */
        $mockExecutionContext = $this->createMockExecutionContext($constraint->messageReservedName);

        $validator->initialize($mockExecutionContext);
        $validator->validate($name, $constraint);
    }

    public function testValidateRejectsExistingUsername()
    {
        $mockRouter = $this->createMock(Router::class);
        $mockUserRepository = $this->createMock(UserRepository::class);
        $mockOrgRepository = $this->createMock(OrganizationRepository::class);

        $mockUserRepository->expects($this->once())->method('count')->willReturn(1);

        $validator = new UserOrgNameValidator($mockRouter, $mockUserRepository, $mockOrgRepository);

        $constraint = new UserOrgName();
        $constraint->payload['type'] = 'org';

        $name = Generator::randomString(4, 39);

        /** @var ExecutionContextInterface $mockExecutionContext */
        $mockExecutionContext = $this->createMockExecutionContext($constraint->messageUniqueName);

        $validator->initialize($mockExecutionContext);
        $validator->validate($name, $constraint);
    }

    public function testValidateRejectsExistingOrgName()
    {
        $mockRouter = $this->createMock(Router::class);
        $mockUserRepository = $this->createMock(UserRepository::class);
        $mockOrgRepository = $this->createMock(OrganizationRepository::class);

        $mockOrgRepository->expects($this->once())->method('countByNameIgnoreCase')->willReturn(1);

        $validator = new UserOrgNameValidator($mockRouter, $mockUserRepository, $mockOrgRepository);

        $constraint = new UserOrgName();
        $constraint->payload['type'] = 'user';

        $name = Generator::randomString(4, 39);

        /** @var ExecutionContextInterface $mockExecutionContext */
        $mockExecutionContext = $this->createMockExecutionContext($constraint->messageUniqueName);

        $validator->initialize($mockExecutionContext);
        $validator->validate($name, $constraint);
    }

    private function createMockExecutionContext(string $expectedViolation)
    {
        $mock = $this->createMock(ExecutionContextInterface::class);
        $mock->expects($this->once())->method('buildViolation')
            ->with($expectedViolation)
            ->willReturn($this->createMockConstraintViolationBuilderInterface());

        return $mock;
    }

    private function createMockConstraintViolationBuilderInterface()
    {
        $mock = $this->createMock(ConstraintViolationBuilderInterface::class);
        $mock->method('setParameter')->willReturnSelf();

        return $mock;
    }
}
