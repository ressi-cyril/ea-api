<?php

namespace App\Controller\Staff\User;

use App\Entity\User\User;
use App\Model\User\Dto\UserDto;
use App\Repository\User\UserRepository;
use App\Service\User\UserService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Requirement\Requirement;

#[OA\Tag('staff_users')]
class StaffUserController extends AbstractFOSRestController
{
    /**
     * Get a list of Users
     */
    #[OA\Response(
        response: 200,
        description: 'Returned when successful',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class))
        )
    )]
    #[Rest\QueryParam('email', default: "", description: 'Specify user mail')]
    #[Rest\Get('/api/staff/users', name: 'staff_get_users')]
    #[Rest\View(serializerGroups: ['read'])]
    public function getUsers(UserRepository $userRepository, ParamFetcher $paramFetcher): array
    {
        return $userRepository->findByCriteria(['gamerTag' => $paramFetcher->get('gamerTag')]);
    }

    /**
     * Get User
     *
     * @param User $user
     * @return User
     */
    #[OA\Response(
        response: 200,
        description: 'Returned when successful',
        content: new Model(type: User::class)
    )]
    #[OA\Response(
        response: 404,
        description: 'Returned when entity not found',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NotFoundHttpException::class))
        )
    )]
    #[Rest\Get('/api/staff/users/{user}', name: 'get_user', requirements: ['user' => Requirement::UUID_V7])]
    #[Rest\View(serializerGroups: ['read'])]
    public function getUserById(User $user): User
    {
        return $user;
    }

    /**
     * Update User
     *
     * @param User $user
     * @param UserDto $userDto
     * @param UserService $userService
     * @return User
     */
    #[OA\Response(
        response: 200,
        description: 'Returned when entity updated',
        content: new Model(type: User::class)
    )]
    #[OA\Response(
        response: 400,
        description: 'Returned when entity has errors',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: HttpException::class))
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Returned when entity not found',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NotFoundHttpException::class))
        )
    )]
    #[OA\Parameter(
        name: 'UserDto',
        content: new OA\JsonContent(
            ref: new Model(type: UserDto::class)
        )
    )]
    #[Rest\Put('/api/staff/users/{user}', name: 'staff_update_user', requirements: ['user' => Requirement::UUID_V7])]
    #[ParamConverter('userDto', class: UserDto::class, converter: "fos_rest.request_body")]
    #[Rest\View(serializerGroups: ['read'])]
    public function updateUser(User $user, UserDto $userDto, UserService $userService): User
    {
        return $userService->updateUser($user, $userDto);
    }

    /**
     * Delete User
     *
     * @param User $user
     * @param UserService $userService
     * @return void
     */
    #[OA\Response(
        response: 204,
        description: 'Returned when successful'
    )]
    #[OA\Response(
        response: 404,
        description: 'Returned when entity not found',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NotFoundHttpException::class))
        )
    )]
    #[Rest\Delete('/api/staff/users/{user}', name: 'staff_delete_user', requirements: ['user' => Requirement::UUID_V7])]
    public function deleteUser(User $user, UserService $userService): void
    {
        $userService->deleteUser($user);
    }
}