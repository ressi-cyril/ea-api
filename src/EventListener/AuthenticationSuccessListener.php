<?php

namespace App\EventListener;

use App\Entity\User\User;
use App\Service\User\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class AuthenticationSuccessListener
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param AuthenticationSuccessEvent $event
     * @return void
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {
        /** @var User $connectedUser */
        $connectedUser = $event->getUser();

        $this->userService->updateConnectedUser($connectedUser);
    }
}