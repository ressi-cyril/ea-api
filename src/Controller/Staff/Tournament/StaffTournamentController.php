<?php

namespace App\Controller\Staff\Tournament;

use App\Entity\Tournament\Tournament;
use App\Entity\Tournament\TournamentMatch;
use App\Repository\Tournament\TournamentMatchRepository;
use App\Repository\Tournament\TournamentRepository;
use App\Service\Ranking\TournamentRankingService;
use App\Service\ValidationService;
use App\Tournament\Dto\TournamentDto;
use App\Tournament\State\InitialState;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Requirement\Requirement;

#[OA\Tag('staff_tournaments')]
class StaffTournamentController extends AbstractFOSRestController
{
    private ManagerRegistry $registry;
    private ValidationService $validationService;
    private TournamentRankingService $rankingService;

    public function __construct(ManagerRegistry $registry, ValidationService $validationService, TournamentRankingService $rankingService)
    {
        $this->registry = $registry;
        $this->validationService = $validationService;
        $this->rankingService = $rankingService;
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
    #[Rest\Get('/api/staff/tournaments', name: 'staff_get_tournaments')]
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
    #[Rest\Get('/api/staff/tournaments/{tournament}', name: 'staff_get_tournament', requirements: ['tournament' => Requirement::UUID_V7])]
    #[Rest\View(serializerGroups: ['myTournament'])]
    public function getTournament(Tournament $tournament): Tournament
    {
        return $tournament;
    }

    /**
     * Post Tournament
     *
     * @param TournamentDto $tournamentDto
     * @return Tournament
     */
    #[OA\Response(
        response: 200,
        description: 'Returned when successful',
        content: new Model(type: Tournament::class)
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
        name: 'TournamentDto',
        content: new OA\JsonContent(
            ref: new Model(type: TournamentDto::class)
        )
    )]
    #[Rest\Post('api/staff/tournaments', name: 'post_tournament')]
    #[ParamConverter('tournamentDto', class: TournamentDto::class, converter: "fos_rest.request_body")]
    #[Rest\View(serializerGroups: ['myTournament'])]
    public function postTournament(TournamentDto $tournamentDto): Tournament
    {
        $initialState = new InitialState();
        $initialState->setDependencies($this->validationService, $this->registry, $this->rankingService);

        $tournament = new Tournament($initialState);
        $tournament->initializeTournament($tournamentDto);

        return $tournament;
    }

    /**
     * Update Tournament
     *
     * @param Tournament $tournament
     * @param TournamentDto $tournamentDto
     * @return Tournament
     * @throws Exception
     */
    #[OA\Response(
        response: 200,
        description: 'Returned when successful',
        content: new Model(type: Tournament::class)
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
        name: 'TournamentDto',
        content: new OA\JsonContent(
            ref: new Model(type: TournamentDto::class)
        )
    )]
    #[Rest\Put('/api/staff/tournaments/{tournament}', name: 'update_tournament', requirements: ['tournament' => Requirement::UUID_V7])]
    #[ParamConverter('tournamentDto', class: TournamentDto::class, converter: "fos_rest.request_body")]
    #[Rest\View(serializerGroups: ["myTournament"])]
    public function updateTournament(Tournament $tournament, TournamentDto $tournamentDto): Tournament
    {
        $tournament->initializeState()->setDependencies($this->validationService, $this->registry, $this->rankingService);
        $tournament->updateTournament($tournamentDto);

        return $tournament;
    }

    /**
     * Delete Tournament
     *
     * @param Tournament $tournament
     * @return void
     * @throws Exception
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
    #[Rest\Delete('/api/staff/tournaments/{tournament}', name: 'delete_tournament', requirements: ['tournament' => Requirement::UUID_V7])]
    public function deleteTournament(Tournament $tournament): void
    {
        $tournament->initializeState()->setDependencies($this->validationService, $this->registry);
        $tournament->deleteTournament();
    }

    /**
     * Get tournament's Matches
     *
     * @param Tournament $tournament
     * @param TournamentMatchRepository $matchRepository
     * @return array
     */
    #[OA\Response(
        response: 200,
        description: 'Returned when successful',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TournamentMatch::class))
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
    #[Rest\Get('/api/staff/tournaments/{tournament}/matches', name: 'staff_get_matches', requirements: ['tournament' => Requirement::UUID_V7])]
    #[Rest\View(serializerGroups: ['myMatch'])]
    public function getTournamentMatches(Tournament $tournament, TournamentMatchRepository $matchRepository): array
    {
        return $matchRepository->findMatchesByTournament($tournament);
    }

}