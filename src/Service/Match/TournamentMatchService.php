<?php

namespace App\Service\Match;

use App\Entity\Tournament\Tournament;
use App\Entity\Tournament\TournamentMatch;
use App\Model\Match\Dto\MatchDto;
use App\Service\UtilsService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TournamentMatchService
{
    private ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * StaffUser update Match
     * @param Tournament $tournament
     * @param TournamentMatch $match
     * @param MatchDto $matchDto
     * @return void
     */
    public function updateMatchResult(Tournament $tournament, TournamentMatch $match, MatchDto $matchDto): void
    {
        $this->validateMatchConditions($tournament, $match, $matchDto, true);

        $match
            ->setResult($matchDto->getScore())
            ->setIsWaitingForAdmin(false)
            ->setIsFinish(true);

        $this->saveTournament($tournament);
    }

    /**
     * Team's captain report Match Score
     *
     * @param Tournament $tournament
     * @param TournamentMatch $match
     * @param MatchDto $matchDto
     * @return void
     */
    public function reportScore(Tournament $tournament, TournamentMatch $match, MatchDto $matchDto): void
    {
        $this->validateMatchConditions($tournament, $match, $matchDto);

        $match->setResult($matchDto->getScore());
        $match->setIsFinish(true);

        $this->saveTournament($tournament);
    }

    /**
     * Team's captain report Match to admin
     *
     * @param Tournament $tournament
     * @param TournamentMatch $match
     * @return void
     */
    public function reportAdmin(Tournament $tournament, TournamentMatch $match): void
    {
        $this->validateMatchConditions($tournament, $match);

        $match->setIsWaitingForAdmin(true);

        $this->saveTournament($tournament);
    }

    /**
     * @param Tournament $tournament
     * @param TournamentMatch $match
     * @param MatchDto|null $matchDto
     * @param bool|null $isStaff
     * @return void
     */
    private function validateMatchConditions(Tournament $tournament, TournamentMatch $match, ?MatchDto $matchDto = null, ?bool $isStaff = false): void
    {
        if ($tournament->getId() !== $match->getRound()->getBracket()->getTournament()->getId()) {
            throw new BadRequestHttpException("Invalid given Match ID");
        }

        // Reporting Match's score (with matchDto)
        if ($matchDto) {
            // player (!isStaff) can't report score if match is already finished (so reported)
            if ($match->isFinish() && !$isStaff) {
                throw new BadRequestHttpException("Match is already finished, report game if needed");
            }

            if (UtilsService::isValidScoreFormat($matchDto->getScore()) === false) {
                throw new BadRequestHttpException("Invalid score format BLA");
            }
        }

        // Reporting or update Match if Round is finish is not possible
        if ($match->getRound()->isFinish()) {
            throw new BadRequestHttpException('Round is already finished and Match cannot be modified or reported');
        }
    }

    /**
     * @param Tournament $tournament
     * @return void
     */
    public function saveTournament(Tournament $tournament): void
    {
        $entityManager = $this->registry->getManager();
        $entityManager->persist($tournament);
        $entityManager->flush();
    }

}