<?php

namespace App\Controller\Tournament;

use App\Entity\Tournament\Tournament;
use App\Entity\Tournament\TournamentMatch;
use App\Model\Match\Dto\MatchDto;
use App\Service\Match\TournamentMatchService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag('tournaments_report')]
class TournamentReportController extends AbstractFOSRestController
{
    public TournamentMatchService $matchService;

    public function __construct(TournamentMatchService $matchService)
    {
        $this->matchService = $matchService;
    }

    /**
     * Report Match score
     *
     * @param Tournament $tournament
     * @param TournamentMatch $match
     * @param MatchDto $matchDto
     * @return void
     */
    #[IsGranted('report_match', 'match')]
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
    #[ParamConverter('matchDto', class: MatchDto::class, converter: "fos_rest.request_body")]
    #[Rest\Patch('/api/tournaments/{tournament}/matches/{match}/report-score', name: 'report_score', requirements: [
        'tournament' => Requirement::UUID_V7,
        'match' => Requirement::UUID_V7
    ])]
    #[Rest\View(serializerGroups: ["report"])]
    public function reportScore(Tournament $tournament, TournamentMatch $match, MatchDto $matchDto): void
    {
        $this->matchService->reportScore($tournament, $match, $matchDto);
    }

    /**
     * Report Match to admin
     *
     * @param Tournament $tournament
     * @param TournamentMatch $match
     * @return void
     */
    #[IsGranted('report_match', 'match')]
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
    #[Rest\Patch('/api/tournaments/{tournament}/matches/{match}/report-admin', name: 'report_admin', requirements: [
        'tournament' => Requirement::UUID_V7,
        'match' => Requirement::UUID_V7
    ])]
    public function reportAdmin(Tournament $tournament, TournamentMatch $match): void
    {
        $this->matchService->reportAdmin($tournament, $match);
    }
}