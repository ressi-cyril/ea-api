<?php

namespace App\Controller\User;

use App\Entity\User\User;
use App\Model\User\Dto\UserDto;
use App\Service\User\UserService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\HttpException;
use OpenApi\Attributes as OA;
use FOS\RestBundle\Controller\Annotations as Rest;

#[OA\Tag('register')]
class UserRegistrationController extends AbstractFOSRestController
{
    /**
     * Post User
     *
     * @param UserDto $userDto
     * @param UserService $userService
     * @return User
     */
    #[OA\Response(
        response: 200,
        description: 'Returned when entity created',
        content: new Model(type: User::class)
    )]
    #[OA\Response(
        response: 400,
        description: 'Returned when entity has errors',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: HttpException::class)))
    )]
    #[OA\Parameter(
        name: 'UserDto',
        content: new OA\JsonContent(
            ref: new Model(type: UserDto::class)
        )
    )]
    #[Rest\Post('/api/open/register', name: 'post_user')]
    #[ParamConverter('userDto', class: UserDto::class, converter: "fos_rest.request_body")]
    #[Rest\View(serializerGroups: ['read'])]
    public function postUser(UserDto $userDto, UserService $userService): User
    {
       return $userService->registerUser($userDto);
    }
}