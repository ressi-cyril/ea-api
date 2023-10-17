<?php

namespace App\Tournament\Interface;

use App\Entity\Team\Team;
use App\Entity\Tournament\Tournament;
use App\Entity\Tournament\TournamentRound;
use App\Model\Round\Dto\RoundDto;
use App\Service\Ranking\TournamentRankingService;
use App\Service\UtilsService;
use App\Service\ValidationService;
use App\Tournament\Dto\TournamentDto;
use App\Tournament\Enum\TournamentStatesEnum;
use Doctrine\Persistence\ManagerRegistry;

interface TournamentStateInterface
{
    /**
     * Initialize new Tournament
     *
     * @param TournamentDto|null $tournamentDto Data for the tournament to be created.
     * @return Tournament  The newly initialized Tournament.
     */
    public function initializeTournament(?TournamentDto $tournamentDto = null): Tournament;

    /**
     * Update Tournament
     *     *
     * @param TournamentDto|null $tournamentDto The updated Tournament.
     * @return Tournament
     */
    public function updateTournament(?TournamentDto $tournamentDto = null): Tournament;

    /**
     * Delete Tournament
     *
     * @return void
     */
    public function deleteTournament(): void;

    /**
     * Team joining or leaving Tournament
     *
     * @param Team|null $team
     * @param bool|null $isJoining
     * @return void
     */
    public function teamJoinOrLeaveTournament(?Team $team = null, ?bool $isJoining = true): void;

    /**
     * Start Tournament
     *
     * @param RoundDto|null $roundDto for generating the tournament rounds
     * @return Tournament
     */
    public function startTournament(?RoundDto $roundDto = null): Tournament;

    /**
     * Continue Tournament progression
     *
     * @param RoundDto|null $roundDto
     * @return void
     */
    public function continueTournament(?RoundDto $roundDto = null): void;

    /**
     * End round allowing to continue tournament progression
     *
     * @param TournamentRound|null $round
     * @return void
     */
    public function endRound(?TournamentRound $round = null): void;

    /**
     * Save tournament to database.
     * @param Tournament $tournament
     * @return void
     */
    public function saveTournament(Tournament $tournament): void;

    /**
     * @param Tournament $tournament
     * @return self
     */
    public function setTournament(Tournament $tournament): self;

    /**
     * @return Tournament
     */
    public function getTournament(): Tournament;

    /**
     * @return TournamentStatesEnum
     */
    public function getStateEnum(): TournamentStatesEnum;

    /**
     * Set needed dependencies, depending on state.
     *
     * @param ValidationService $validationService
     * @param ManagerRegistry $registry
     * @param TournamentRankingService|null $rankingService
     * @param UtilsService|null $utils
     * @return void
     */
    public function setDependencies(
        ValidationService $validationService,
        ManagerRegistry $registry,
        ?TournamentRankingService $rankingService = null,
        ?UtilsService $utils = null
    ): void;
}