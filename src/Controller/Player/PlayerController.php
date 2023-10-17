<?php

namespace App\Controller\Player;

use App\Entity\User\PlayerUser;
use App\Entity\User\User;
use App\Repository\User\PlayerUserRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Requirement\Requirement;

#[OA\Tag('players')]
class PlayerController extends AbstractFOSRestController
{
    /**
     * Get a list of Players
     *
     * @param PlayerUserRepository $playerRepository
     * @param ParamFetcher $paramFetcher
     * @return array
     */
    #[OA\Response(
        response: 200,
        description: 'Returned when successful',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class))
        )
    )]
    #[Rest\QueryParam('gamerTag', default: "", description: 'Specify player gamerTag')]
    #[Rest\Get('/api/players', name: 'get_players')]
    #[Rest\View(serializerGroups: ['read'])]
    public function getPlayers(PlayerUserRepository $playerRepository, ParamFetcher $paramFetcher): array
    {
        return $playerRepository->findByCriteria(['gamerTag' => $paramFetcher->get('gamerTag')]);
    }

    /**
     * Get Player
     *
     * @param PlayerUser $playerUser
     * @return PlayerUser
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
    #[Rest\Get('/api/players/{player}', name: 'get_player', requirements: ['player' => Requirement::UUID_V7])]
    #[Rest\View(serializerGroups: ['read'])]
    public function getPlayerById(PlayerUser $playerUser): PlayerUser
    {
        return $playerUser;
    }
}