<?php

namespace App\Tournament\State;

use App\Entity\Team\Team;
use App\Entity\Tournament\Tournament;
use App\Entity\Tournament\TournamentRound;
use App\Model\Round\Dto\RoundDto;
use App\Service\Ranking\TournamentRankingService;
use App\Service\UtilsService;
use App\Service\ValidationService;
use App\Tournament\Dto\TournamentDto;
use App\Tournament\Enum\TournamentStatesEnum;
use App\Tournament\Interface\TournamentStateInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class FinishedState implements TournamentStateInterface
{
    private Tournament $tournament;

    /**
     * {@inheritdoc} not available in this state
     */
    public function initializeTournament(?TournamentDto $tournamentDto = null): Tournament
    {
        throw new BadRequestHttpException('Invalid operation: "initializeTournament" is not allowed in "FinishedState" state.');
    }

    /**
     * {@inheritdoc} not available in this state
     */
    public function updateTournament(?TournamentDto $tournamentDto = null): Tournament
    {
        throw new BadRequestHttpException('Invalid operation: "updateTournament" is not allowed in "FinishedState" state.');
    }

    /**
     * {@inheritdoc} not available in this state
     */
    public function deleteTournament(): void
    {
        throw new BadRequestHttpException('Invalid operation: "deleteTournament" is not allowed in "FinishedState" state.');
    }

    /**
     * {@inheritdoc} not available in this state
     */
    public function teamJoinOrLeaveTournament(?Team $team = null, ?bool $isJoining = true): void
    {
        throw new BadRequestHttpException('Invalid operation: "teamJoinOrLeaveTournament" is not allowed in "InProgress" state.');
    }

    /**
     * {@inheritdoc} not available in this state
     */
    public function startTournament(?RoundDto $roundDto = null): Tournament
    {
        throw new BadRequestHttpException('Invalid operation: "startTournament" is not allowed in "FinishedState" state.');
    }

    /**
     * {@inheritdoc} not available in this state
     */
    public function continueTournament(?RoundDto $roundDto = null): void
    {
        throw new BadRequestHttpException('Invalid operation: "continueTournament" is not allowed in "FinishedState" state.');
    }

    /**
     * {@inheritdoc} not available in this state
     */
    public function endRound(?TournamentRound $round = null): void
    {
        throw new BadRequestHttpException('Invalid operation: "endRound" is not allowed in "FinishedState" state.');
    }

    /**
     * {@inheritdoc} never used in this state
     */
    public function saveTournament(Tournament $tournament): void
    {
        // Interface method, never need.
    }

    /**
     * {@inheritdoc}
     */
    public function setTournament(Tournament $tournament): self
    {
        $this->tournament = $tournament;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTournament(): Tournament
    {
        return $this->tournament;
    }

    /**
     * {@inheritdoc}
     */
    public function getStateEnum(): TournamentStatesEnum
    {
        return TournamentStatesEnum::FINISHED;
    }

    /**
     * {@inheritdoc}
     */
    public function setDependencies(
        ValidationService $validationService,
        ManagerRegistry $registry,
        ?TournamentRankingService $rankingService = null,
        ?UtilsService $utils = null
    ): void {
        // Interface method, never need.
    }
}