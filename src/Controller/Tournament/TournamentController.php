<?php

namespace App\Controller\Tournament;

use App\Entity\Team\Team;
use App\Entity\Tournament\Tournament;
use App\Repository\Tournament\TournamentRepository;
use App\Service\ValidationService;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag('tournaments')]
class TournamentController extends AbstractFOSRestController
{
    private ManagerRegistry $registry;
    private ValidationService $validationService;

    public function __construct(ManagerRegistry $registry, ValidationService $validationService)
    {
        $this->registry = $registry;
        $this->validationService = $validationService;
    }

    /**
     * Get a list of Tournaments
     *
     * @param TournamentRepository $tournamentRepository
     * @return array
     */
    #[OA\Response(
        response: 200,
        description: 'Returned when successful',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Tournament::class))
        )
    )]
    #[Rest\Get('/api/tournaments', name: 'get_tournaments')]
    #[Rest\View(serializerGroups: ['myTournament'])]
    public function getTournaments(TournamentRepository $tournamentRepository): array
    {
        return $tournamentRepository->findAll();
    }

    /**
     * Get Tournament
     *
     * @param Tournament $tournament
     * @return Tournament
     */
    #[OA\Response(
        response: 200,
        description: 'Returned when successful',
        content: new Model(type: Tournament::class)
    )]
    #[OA\Response(
        response: 404,
        description: 'Returned when entity not found',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NotFoundHttpException::class))
        )
    )]
    #[Rest\Get('/api/tournaments/{tournament}', name: 'get_tournament', requirements: ['tournament' => Requirement::UUID_V7])]
    #[Rest\View(serializerGroups: ['myTournament'])]
    public function getTournament(Tournament $tournament): Tournament
    {
        return $tournament;
    }

    /**
     * Team join Tournament
     *
     * @param Tournament $tournament
     * @param Team $team
     * @return void
     * @throws Exception
     */
    #[IsGranted('join_tournament', 'team')]
    #[OA\Response(
        response: 204,
        description: 'Returned when successful',
        content: new Model(type: Tournament::class)
    )]
    #[OA\Response(
        response: 404,
        description: 'Returned when entity not found',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NotFoundHttpException::class))
        )
    )]
    #[Rest\Post('/api/tournaments/{tournament}/teams/{team}/join', name: 'team_join_tournament', requirements: [
        'tournament' => Requirement::UUID_V7,
        'team' => Requirement::UUID_V7
    ])]
    #[Rest\View(serializerGroups: ["myTournament"])]
    public function teamJoinTournament(Tournament $tournament, Team $team): void
    {
        $tournament->initializeState()->setDependencies($this->validationService, $this->registry);
        $tournament->teamJoinOrLeaveTournament($team, true);
    }

    /**
     * Team leave Tournament
     *
     * @param Tournament $tournament
     * @param Team $team
     * @return void
     * @throws Exception
     */
    #[IsGranted('leave_tournament', 'team')]
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
    #[Rest\Delete('/api/tournaments/{tournament}/teams/{team}/leave', name: 'team_leave_tournament', requirements: [
        'tournament' => Requirement::UUID_V7,
        'team' => Requirement::UUID_V7
    ])]
    #[Rest\View(serializerGroups: ["myTournament"])]
    public function teamLeaveTournament(Tournament $tournament, Team $team): void
    {
        $tournament->initializeState()->setDependencies($this->validationService, $this->registry);
        $tournament->teamJoinOrLeaveTournament($team, false);
    }

}