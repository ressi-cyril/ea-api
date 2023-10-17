<?php

namespace App\Service\User;

use App\Entity\User\PlayerUser;
use App\Entity\User\StaffUser;
use App\Entity\User\User;
use App\Model\User\Dto\UserDto;
use App\Model\User\Enum\UserEnum;
use App\Repository\User\UserRepository;
use App\Service\ValidationService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private ManagerRegistry $registry;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private ValidationService $validationService;

    /***
     * @param ManagerRegistry $registry
     * @param UserRepository $userRepository
     * @param UserPasswordHasher $passwordHasher
     * @param ValidationService $validationService
     */
    public function __construct(ManagerRegistry $registry, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, ValidationService $validationService)
    {
        $this->registry = $registry;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->validationService = $validationService;
    }

    /**
     * Register a new user.
     *
     * @param UserDto $userDto The user data transfer object.
     * @return User The registered user.
     */
    public function registerUser(UserDto $userDto): User
    {
        // Verify that the DTO has all required fields
        $this->validationService->performEntityValidation($userDto, ['userRegister']);

        // Create a User instance based on user type
        $user = match ($userDto->type) {
            UserEnum::PLAYER->value => $this->createUser($userDto, PlayerUser::class, ['playerRegister', 'userRegister']),
            UserEnum::STAFF->value => $this->createUser($userDto, StaffUser::class, ['staffRegister', 'userRegister']),
            default => throw new BadRequestHttpException('Invalid user type', null, 400),
        };

        // Save the user to the database
        $this->saveUser($user);

        return $user;
    }

    /**
     * Create a new User instance based on the provided user type.
     *
     * @param UserDto $userDto The user data transfer object.
     * @param string $userClass The class name of the user type.
     * @param array $validationGroups The validation groups for the user.
     * @return User The created user.
     */
    private function createUser(UserDto $userDto, string $userClass, array $validationGroups): User
    {
        // Check if the email is already used
        if ($this->userRepository->findOneBy(['email' => $userDto->email])) {
            throw new BadRequestHttpException('Email is already used');
        }

        // Verify that the DTO has all the required fields
        $this->validationService->performEntityValidation($userDto, $validationGroups);

        // Create a new instance of the user class
        $user = new $userClass();
        $user
            ->setEmail($userDto->email)
            ->setPassword($this->passwordHasher->hashPassword($user, $userDto->password))
            ->setIsEnabled(true)
            ->setCreatedAt(new \DateTime('now'));

        if ($user instanceof PlayerUser) {
            $user
                ->setGamerTag($userDto->gamerTag)
                ->setPoints(0)
                ->setRoles([UserEnum::ROLE_PLAYER->value])
                ->setIsCaptain(false);
        } elseif ($user instanceof StaffUser) {
            $user
                ->setName($userDto->name)
                ->setRoles([UserEnum::ROLE_STAFF->value]);
        }

        // Verify that the created user object is valid
        $this->validationService->performEntityValidation($user, $validationGroups);

        return $user;
    }

    /**
     *
     * Update a user information based on the provided UserDto.
     *
     * @param User $user The original User
     * @param UserDto $userDto The user data transfer object
     * @return User The updated User
     */
    public function updateUser(User $user, UserDto $userDto): User
    {
        $this->copyFromUserDto($user, $userDto);

        $validationGroups[] = 'userUpdate';

        if ($user instanceof PlayerUser) {
            $validationGroups[] = 'playerUpdate';
        } elseif ($user instanceof StaffUser) {
            $validationGroups[] = 'staffUpdate';
        }

        $this->validationService->performEntityValidation($user, $validationGroups);

        $this->saveUser($user);

        return $user;
    }

    /**
     * Delete User
     *
     * @param User $user
     * @return void
     */
    public function deleteUser(User $user): void
    {
        $entityManager = $this->registry->getManager();
        $entityManager->remove($user);
        $entityManager->flush();
    }

    /**
     * Update User's information on Auth success
     *
     * @param User $user
     * @return void
     */
    public function updateConnectedUser(User $user): void
    {
        $user->setLastLogin(new \DateTime());

        $this->registry->getManager()->flush();
    }

    /**
     * Copy the data from the UserDto to user
     *
     * @param User $user The original user
     * @param UserDto $userDto The user data transfer object.
     */
    private function copyFromUserDto(User $user, UserDto $userDto): void
    {
        // Change value only if they are different and sets, prevent duplicate key errors
        if (isset($userDto->email) && $userDto->email !== $user->getEmail()) {
            $user->setEmail($userDto->email);
        }

        if (isset($userDto->gamerTag) && $user instanceof PlayerUser && $user->getGamerTag() !== $userDto->gamerTag) {
            $user->setGamerTag($userDto->gamerTag);
        }

        if (isset($userDto->name) && $user instanceof StaffUser && $user->getName() !== $userDto->name) {
            $user->setName($userDto->name);
        }
    }

    /**
     * save User to database.
     *
     * @param User $user
     * @return void
     */
    private function saveUser(User $user): void
    {
        // Save team to Database
        $entityManager = $this->registry->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
    }

}
