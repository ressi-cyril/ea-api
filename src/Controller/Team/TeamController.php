<?php

namespace App\Controller\Team;

use App\Entity\Team\Team;
use App\Entity\Team\TeamInvite;
use App\Entity\User\PlayerUser;
use App\Model\Team\Dto\TeamDto;
use App\Repository\Team\TeamRepository;
use App\Service\Team\TeamService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag('teams')]
class TeamController extends AbstractFOSRestController
{
    /**
     * Get a list of Teams
     *
     * @param TeamRepository $teamRepository
     * @return array
     */
    #[OA\Response(
        response: 200,
        description: 'Returned when successful',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Team::class))
        )
    )]
    #[Rest\Get('/api/teams', name: 'get_teams')]
    #[Rest\View(serializerGroups: ['myTeam'])]
    public function getTeams(TeamRepository $teamRepository): array
    {
        return $teamRepository->findAll();
    }

    /**
     * Get Team
     *
     * @param Team $team
     * @return Team
     */
    #[OA\Response(
        response: 200,
        description: 'Returned when successful',
        content: new Model(type: Team::class)
    )]
    #[OA\Response(
        response: 404,
        description: 'Returned when entity not found',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NotFoundHttpException::class))
        )
    )]
    #[Rest\Get('/api/teams/{team}', name: 'get_team', requirements: ['team' => Requirement::UUID_V7])]
    #[Rest\View(serializerGroups: ['myTeam'])]
    public function getTeam(Team $team): Team
    {
        return $team;
    }

    /**
     * Post Team
     *
     * @param TeamDto $teamDto
     * @param TeamService $teamService
     * @return Team
     */
    #[IsGranted('IS_AUTHENTICATED')]
    #[OA\Response(
        response: 200,
        description: 'Returned when entity created',
        content: new Model(type: Team::class)
    )]
    #[OA\Response(
        response: 400,
        description: 'Returned when entity has errors',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: HttpException::class))
        )
    )]
    #[OA\Parameter(
        name: 'TeamDto',
        content: new OA\JsonContent(
            ref: new Model(type: TeamDto::class)
        )
    )]
    #[Rest\Post('api/teams', name: 'post_team')]
    #[ParamConverter('teamDto', class: TeamDto::class, converter: "fos_rest.request_body")]
    #[Rest\View(serializerGroups: ['myTeam'])]
    public function postTeam(TeamDto $teamDto, TeamService $teamService): Team
    {
        return $teamService->createTeam($teamDto);
    }

    /**
     * Update Team
     *
     * @param Team $team
     * @param TeamDto $teamDto
     * @param TeamService $teamService
     * @return Team
     */
    #[IsGranted('update_team', 'team')]
    #[OA\Response(
        response: 200,
        description: 'Returned when successful',
        content: new Model(type: Team::class)
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
        name: 'TeamDto',
        content: new OA\JsonContent(
            ref: new Model(type: TeamDto::class)
        )
    )]
    #[Rest\Put('/api/teams/{team}', name: 'update_team', requirements: ['team' => Requirement::UUID_V7])]
    #[ParamConverter('teamDto', class: TeamDto::class, converter: "fos_rest.request_body")]
    #[Rest\View(serializerGroups: ["myTeam"])]
    public function updateTeam(Team $team, TeamDto $teamDto, TeamService $teamService): Team
    {
        return $teamService->updateTeam($team, $teamDto);
    }

    /**
     * Delete Team
     *
     * @param Team $team
     * @param TeamService $teamService
     * @return void
     */
    #[IsGranted('delete_team', 'team')]
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
    #[Rest\Delete('/api/teams/{team}', name: 'delete_team', requirements: ['team' => Requirement::UUID_V7])]
    public function deleteTeam(Team $team, TeamService $teamService): void
    {
        $teamService->deleteTeam($team);
    }

    /**
     * Team invite Player to join
     *
     * @param Team $team
     * @param PlayerUser $player
     * @param TeamService $teamService
     * @return TeamInvite
     */
    #[IsGranted('invite_team', 'team')]
    #[OA\Response(
        response: 200,
        description: 'Returned when successful',
        content: new Model(type: TeamInvite::class)
    )]
    #[OA\Response(
        response: 404,
        description: 'Returned when entity not found',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NotFoundHttpException::class))
        )
    )]
    #[Rest\Post('/api/teams/{team}/players/{player}/invite', name: 'invite_player_team', requirements: ['team' => Requirement::UUID_V7, 'player' => Requirement::UUID_V7])]
    #[Rest\View(serializerGroups: ["myInvite"])]
    public function invitePlayer(Team $team, PlayerUser $player, TeamService $teamService): TeamInvite
    {
        return $teamService->invitePlayer($team, $player);
    }

    /**
     * Player decline TeamInvite
     *
     * @param Team $team
     * @param PlayerUser $player
     * @param TeamService $teamService
     * @return void
     */
    #[IsGranted('decline_team', 'player')]
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
    #[Rest\Delete('/api/teams/{team}/players/{player}/decline', name: 'player_decline_team', requirements: ['team' => Requirement::UUID_V7, 'player' => Requirement::UUID_V7])]
    public function playerDeclineTeam(Team $team, PlayerUser $player, TeamService $teamService): void
    {
        $teamService->playerDecline($team, $player);
    }

    /**
     * Player join Team
     *
     * @param Team $team
     * @param PlayerUser $player
     * @param TeamService $teamService
     * @return Team
     */
    #[IsGranted('join_team', 'player')]
    #[OA\Response(
        response: 200,
        description: 'Returned when successful',
        content: new Model(type: Team::class)
    )]
    #[OA\Response(
        response: 404,
        description: 'Returned when entity not found',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NotFoundHttpException::class))
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Returned when entity has errors',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: HttpException::class))
        )
    )]
    #[Rest\Post('/api/teams/{team}/players/{player}/join', name: 'player_join_team', requirements: ['team' => Requirement::UUID_V7, 'player' => Requirement::UUID_V7])]
    #[Rest\View(serializerGroups: ["myTeam"])]
    public function playerJoinTeam(Team $team, PlayerUser $player, TeamService $teamService): Team
    {
        return $teamService->playerJoinTeam($team, $player);
    }

    /**
     * Player leave Team
     *
     * @param Team $team
     * @param PlayerUser $player
     * @param TeamService $teamService
     * @return void
     */
    #[IsGranted('leave_team', 'player')]
    #[OA\Response(
        response: 204,
        description: 'Returned when successful',
    )]
    #[OA\Response(
        response: 404,
        description: 'Returned when entity not found',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NotFoundHttpException::class))
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Returned when entity has errors',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: HttpException::class))
        )
    )]
    #[Rest\Delete('/api/teams/{team}/players/{player}/leave', name: 'player_leave_team', requirements: ['team' => Requirement::UUID_V7, 'player' => Requirement::UUID_V7])]
    #[Rest\View(serializerGroups: ["myTeam"])]
    public function playerLeaveTeam(Team $team, PlayerUser $player, TeamService $teamService): void
    {
        $teamService->playerLeaveTeam($team, $player);
    }

}