<?php

namespace App\Controller\Staff\Tournament;

use App\Entity\Tournament\Tournament;
use App\Entity\Tournament\TournamentMatch;
use App\Entity\Tournament\TournamentRound;
use App\Model\Match\Dto\MatchDto;
use App\Model\Round\Dto\RoundDto;
use App\Service\Match\TournamentMatchService;
use App\Service\Ranking\TournamentRankingService;
use App\Service\UtilsService;
use App\Service\ValidationService;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Requirement\Requirement;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Attributes as OA;

#[OA\Tag('staff_tournaments_progression')]
class StaffTournamentProgressionController extends AbstractFOSRestController
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
     * Start Tournament
     *
     * @param Tournament $tournament
     * @param RoundDto $roundDto
     * @param UtilsService $utils
     * @return void
     * @throws Exception
     */
    #[OA\Response(
        response: 204,
        description: 'Returned when successful',
    )]
    #[OA\Response(
        response: 400,
        description: 'Returned when errors',
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
        name: 'RoundDto',
        content: new OA\JsonContent(
            ref: new Model(type: RoundDto::class)
        )
    )]
    #[ParamConverter('roundDto', class: RoundDto::class, converter: "fos_rest.request_body")]
    #[Rest\Post('/api/staff/tournaments/{tournament}/start', name: 'start_tournament', requirements: ['tournament' => Requirement::UUID_V7])]
    #[Rest\View(serializerGroups: ["start"])]
    public function startTournament(Tournament $tournament, RoundDto $roundDto, UtilsService $utils): void
    {
        $tournament->initializeState()->setDependencies($this->validationService, $this->registry, $this->rankingService, $utils);
        $tournament->startTournament($roundDto);
    }

    /**
     * Continue Tournament progression
     *
     * @throws Exception
     */
    #[OA\Response(
        response: 204,
        description: 'Returned when successful',
    )]
    #[OA\Response(
        response: 400,
        description: 'Returned when errors',
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
        name: 'RoundDto',
        content: new OA\JsonContent(
            ref: new Model(type: RoundDto::class)
        )
    )]
    #[ParamConverter('roundDto', class: RoundDto::class, converter: "fos_rest.request_body")]
    #[Rest\Post('api/staff/tournaments/{tournament}/continue', name: 'continue_tournament', requirements: ['tournament' => Requirement::UUID_V7])]
    #[Rest\View(serializerGroups: ["next"])]
    public function continueTournament(Tournament $tournament, RoundDto $roundDto, UtilsService $utils): void
    {
        $tournament->initializeState()->setDependencies($this->validationService, $this->registry, $this->rankingService, $utils);
        $tournament->continueTournament($roundDto);
    }

    /**
     * End given round
     *
     * @throws Exception
     */
    #[OA\Response(
        response: 204,
        description: 'Returned when successful',
    )]
    #[OA\Response(
        response: 400,
        description: 'Returned when errors',
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
    #[Rest\Patch('api/staff/tournaments/{tournament}/rounds/{round}/end', name: 'end_round', requirements: ['tournament' => Requirement::UUID_V7, 'round' => Requirement::UUID_V7])]
    public function endRound(Tournament $tournament, TournamentRound $round): void
    {
        $tournament->initializeState()->setDependencies($this->validationService, $this->registry);
        $tournament->endRound($round);
    }

    /**
     * Update Match result
     *
     * @param Tournament $tournament
     * @param TournamentMatch $match
     * @param MatchDto $matchDto
     * @param TournamentMatchService $matchService
     * @return void
     */
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
    #[ParamConverter('matchDto', class: MatchDto::class, converter: "fos_rest.request_body")]
    #[Rest\Patch('/api/staff/tournaments/{tournament}/matches/{match}', name: 'staff_update_matches', requirements: [
        'tournament' => Requirement::UUID_V7,
        'match' => Requirement::UUID_V7
    ])]
    #[Rest\View(serializerGroups: ['myMatch'])]
    public function updateTournamentMatchResult(Tournament $tournament, TournamentMatch $match, MatchDto $matchDto, TournamentMatchService $matchService): void
    {
        $matchService->updateMatchResult($tournament, $match, $matchDto);
    }
}
