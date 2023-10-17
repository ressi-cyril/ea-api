<?php

namespace App\Tournament\State;

use App\Entity\Team\Team;
use App\Entity\Team\TeamFake;
use App\Entity\Tournament\Tournament;
use App\Entity\Tournament\TournamentBracket;
use App\Entity\Tournament\TournamentMatch;
use App\Entity\Tournament\TournamentMatchFinal;
use App\Entity\Tournament\TournamentRound;
use App\Model\Bracket\Enum\BracketEnum;
use App\Model\Round\Dto\RoundDto;
use App\Repository\Tournament\TournamentRoundRepository;
use App\Service\Ranking\TournamentRankingService;
use App\Service\UtilsService;
use App\Service\ValidationService;
use App\Tournament\Dto\TournamentDto;
use App\Tournament\Enum\TournamentStatesEnum;
use App\Tournament\Interface\TournamentStateInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class InProgressState implements TournamentStateInterface
{
    const FINAL = 'Final';
    const SEMI_FINAL = 'Semi Final';
    const WINNER_TEAM = 'winner';
    const LOSER_TEAM = 'loser';
    const GRAND_FINAL_RESET = 'Grand Final Reset';
    const GRAND_FINAL = 'Grand Final';
    const WINNER_FINAL = 'Winner Final';
    private ManagerRegistry $registry;
    private ?TournamentRankingService $rankingService = null;
    private Tournament $tournament;
    private TournamentRoundRepository $tournamentRoundRepository;
    private ?UtilsService $utils = null;

    /**
     * {@inheritdoc} not available in this state
     */
    public function initializeTournament(?TournamentDto $tournamentDto = null): Tournament
    {
        throw new BadRequestHttpException('Invalid operation: "initializeTournament" is not allowed in "InProgress" state.');
    }

    /**
     * {@inheritdoc} not available in this state
     */
    public function updateTournament(?TournamentDto $tournamentDto = null): Tournament
    {
        throw new BadRequestHttpException('Invalid operation: "updateTournament" is not allowed in "InProgress" state.');
    }

    /**
     * {@inheritdoc} not available in this state
     */
    public function deleteTournament(?Tournament $tournament = null): void
    {
        throw new BadRequestHttpException('Invalid operation: "deleteTournament" is not allowed in "InProgress" state.');
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
        throw new BadRequestHttpException('Invalid operation: "startTournament" is not allowed in "InProgress" state.');
    }

    /**
     * {@inheritdoc}
     * @throws NonUniqueResultException
     */
    public function continueTournament(?RoundDto $roundDto = null): void
    {
        $tournament = $this->getTournament();

        if (!$this->verifyRoundsAreOver($this->tournamentRoundRepository->getAllRoundsByTournament($tournament))) {
            throw new BadRequestHttpException("Not all rounds are over, can't continue tournament");
        }

        $finalStageReached = $this->createNextWinnerRound($tournament, $roundDto);

        if ($tournament->hasLoserBracket() && !$finalStageReached) {
            $this->createNextLoserRound($tournament, $roundDto);
        }

        $this->saveTournament($tournament);
    }

    /**
     * {@inheritdoc}
     */
    public function endRound(?TournamentRound $round = null): void
    {
        if (!$round->verifyMatchesAreOver()) {
            throw new BadRequestHttpException('All matches are not over or waiting for admin');
        };

        if ($this->tournament !== $round->getBracket()->getTournament()) {
            throw new BadRequestHttpException("Round Tournament doesn't match given Tournament");
        }

        $round->setIsFinish(true);

        $this->saveTournament($this->tournament);
    }

    /**
     * {@inheritdoc}
     */
    public function saveTournament(Tournament $tournament): void
    {
        $entityManager = $this->registry->getManager();
        $entityManager->persist($tournament);
        $entityManager->flush();
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
    public function getStateEnum(): TournamentStatesEnum
    {
        return TournamentStatesEnum::IN_PROGRESS;
    }

    /**
     * {@inheritdoc}
     */
    public function getTournament(): Tournament
    {
        return $this->tournament;
    }

    /**
     * {@inheritdoc} not available in this state
     */
    public function setDependencies(
        ValidationService $validationService,
        ManagerRegistry $registry,
        ?TournamentRankingService $rankingService = null,
        ?UtilsService $utils = null
    ): void {
        $this->registry = $registry;
        $this->tournamentRoundRepository = $this->registry->getRepository(TournamentRound::class);

        if ($utils) {
            $this->utils = $utils;
        }

        if ($rankingService) {
            $this->rankingService = $rankingService;
        }
    }

    /**
     * Business logic of WinnerRound creation.
     *
     * @param Tournament $tournament
     * @param RoundDto $roundDto
     * @return bool
     * @throws NonUniqueResultException
     */
    private function createNextWinnerRound(Tournament $tournament, RoundDto $roundDto): bool
    {
        // Retrieve results of the latest winner round
        $winnerBracket = $this->registry->getRepository(TournamentBracket::class)->findBracketByTournament($tournament, BracketEnum::WINNER_BRACKET->value);
        $lastWinnerRound = $this->registry->getRepository(TournamentRound::class)->getLastFinishedRoundFromBracket($winnerBracket);
        $lastWinnerRoundResults = $this->retrieveRoundResults($lastWinnerRound);
        $winningTeamsFromWinnerRound = $this->getTeamsFromResults($lastWinnerRoundResults, self::WINNER_TEAM);
        $finalStageReached = false;

        //verify if last game is Final
        $lastGame = $lastWinnerRound->getMatches()->last();
        if ($lastGame instanceof TournamentMatchFinal) {
            $finalStageReached = $this->processFinal($tournament, $lastGame, $roundDto);
        } else {
            $nextWinnerRound = $this->populateRound(
                $roundDto,
                true,
                count($winningTeamsFromWinnerRound),
                count($winnerBracket->getTournamentRounds())
            );

            $this->createsMatchesForRound(
                $nextWinnerRound,
                $winningTeamsFromWinnerRound,
                $this->utils->getNextLetter($this->utils->getLastLetter($lastWinnerRoundResults[self::WINNER_TEAM])),
            );

            $winnerBracket->addTournamentRound($nextWinnerRound);
        }

        // Assign losing Teams to Ranking Tiers
        if (!$tournament->hasLoserBracket()) {
            $this->rankingService->assignTeamsToTiers($tournament, $this->getTeamsFromResults($lastWinnerRoundResults, self::LOSER_TEAM));
        }

        return $finalStageReached;
    }

    /**
     * Business logic of LoserRound creation, for details google "x teams double elimination brackets".
     *
     * @param Tournament $tournament
     * @param RoundDto $roundDto
     * @return void
     * @throws NonUniqueResultException
     */
    private function createNextLoserRound(Tournament $tournament, RoundDto $roundDto): void
    {
        // Retrieve data
        $loserBracket = $this->registry->getRepository(TournamentBracket::class)->findBracketByTournament($tournament, BracketEnum::LOSER_BRACKET->value);
        $winnerBracket = $this->registry->getRepository(TournamentBracket::class)->findBracketByTournament($tournament, BracketEnum::WINNER_BRACKET->value);

        // Initialize variables
        $startingLetter = 'A';
        $loserRoundsCount = count($loserBracket->getTournamentRounds());
        $teamsForMatches = [];

        // Determine teams for matches based on conditions
        if ($loserRoundsCount === 0) {
            // No previous loser rounds, use losing teams from the last finished winner round
            $lastWinnerRound = $this->tournamentRoundRepository->getLastFinishedRoundFromBracket($winnerBracket);
            $lastWinnerRoundResults = $this->retrieveRoundResults($lastWinnerRound);
            $teamsForMatches = $this->getTeamsFromResults($lastWinnerRoundResults, self::LOSER_TEAM);
        } elseif ($loserRoundsCount > 0) {
            // Previous loser rounds exist, use winning teams from the last finished loser round
            $lastLoserRound = $this->tournamentRoundRepository->getLastFinishedRoundFromBracket($loserBracket);
            $loserRoundResults = $this->retrieveRoundResults($lastLoserRound);
            $winningTeamsFromLR = $this->getTeamsFromResults($loserRoundResults, self::WINNER_TEAM);
            $startingLetter = $this->utils->getNextLetter($this->utils->getLastLetter($loserRoundResults[self::WINNER_TEAM])[1]);

            // odd loser round count
            if ($loserRoundsCount % 2 !== 0) {
                // loserRound n to create = Winners from previous LoserRound vs Losers from WinningRound (n/2+1)
                $n = $loserRoundsCount + 1;
                $index = ($n / 2 + 1) - 1;

                $winnerRound = $winnerBracket->getTournamentRounds()[$index];
                $winnerRoundResults = $this->retrieveRoundResults($winnerRound);
                $losingTeamsFromWR = $this->getTeamsFromResults($winnerRoundResults, self::LOSER_TEAM);

                $teamsForMatches = $this->getCombinedTeamForLoserRound($winningTeamsFromLR, $losingTeamsFromWR);
            } else {
                // For even loser round count, create matches with Winning teams from previous loser round
                $teamsForMatches = $winningTeamsFromLR;
            }

            // Assign losing Teams to Ranks Tiers
            $this->rankingService->assignTeamsToTiers($tournament, $this->getTeamsFromResults($loserRoundResults, self::LOSER_TEAM));
        }

        // Create next Loser Round
        $nextLoserRound = $this->populateRound($roundDto, false, count($teamsForMatches), $loserRoundsCount);
        $this->createsMatchesForRound($nextLoserRound, $teamsForMatches, $startingLetter, true);
        $loserBracket->addTournamentRound($nextLoserRound);
    }

    /**
     * Process the final steps in the tournament logic.
     * It checks if a loser bracket exists, and if so, it processes the grand final.
     * If all matches are over, generate final tournament ranking
     *
     * @param Tournament $tournament
     * @param TournamentMatchFinal $matchFinal
     * @param RoundDto $roundDto
     * @return bool
     * @throws NonUniqueResultException
     */
    private function processFinal(Tournament $tournament, TournamentMatchFinal $matchFinal, RoundDto $roundDto): bool
    {
        $tournamentIsFinish = true;
        $teamsToTier = [];
        $teamsForMatches = [];
        $finalName = "";
        $tournamentBracketRepository = $this->registry->getRepository(TournamentBracket::class);

        if ($tournament->hasLoserBracket()) {
            $loserBracket = $tournamentBracketRepository->findBracketByTournament($tournament, BracketEnum::LOSER_BRACKET->value);
            $loserRound = $tournamentBracketRepository->getLastFinishedRoundFromBracket($loserBracket);

            if (!$loserRound->getMatches()[0] instanceof TournamentMatchFinal) {
                return false;
            }

            $teamsForMatches[] = $matchFinal->getWinningTeam();

            // Determine the next stage based on the current stage of the tournament.
            if ($matchFinal->getRound()->getName() === self::WINNER_FINAL) {
                // Grand final is needed
                $loserFinalMatch = $loserRound->getMatches()[0];
                $teamsForMatches[] = $loserFinalMatch->getWinningTeam();
                $finalName = self::GRAND_FINAL;
                $tournamentIsFinish = false;

                $teamsToTier[] = $loserFinalMatch->getLosingTeam();
            } elseif ($matchFinal->getName() === self::GRAND_FINAL && $matchFinal->getWinningTeam() !== $matchFinal->getTeamOne()) {
                // Grand Final Reset is needed when Loser from Grand Final come from the WinnerBracket
                $teamsForMatches[] = $matchFinal->getLosingTeam();
                $finalName = self::GRAND_FINAL_RESET;
                $tournamentIsFinish = false;
            }
        }

        // If there's a new final round to be created, proceed with its setup
        if ($finalName) {
            $winnerBracket = $tournamentBracketRepository->findBracketByTournament($tournament, BracketEnum::WINNER_BRACKET->value);
            $round = $this->populateRound($roundDto, true, 2);
            $round->setName($finalName);
            $this->createsMatchesForRound($round, $teamsForMatches, null, false, $finalName);
            $winnerBracket->addTournamentRound($round);
            $this->registry->getManager()->persist($round);
        }

        // If Tournament is finish, process ending.
        if ($tournamentIsFinish) {
            $teamsToTier[] = $matchFinal->getLosingTeam();
            $teamsToTier[] = $matchFinal->getWinningTeam();
            $this->rankingService->assignTeamsToTiers($tournament, $teamsToTier);

            $this->endTournament($tournament);
        } else {
            $this->rankingService->assignTeamsToTiers($tournament, $teamsToTier);
        }


        return true;
    }

    /**
     * Creates all required Matches for Round
     *
     * @param TournamentRound $round
     * @param array $teams
     * @param string|null $startingLetter
     * @param bool|null $isLoser for LoserBracket ? true : false
     * @param string|null $finalMatchName
     * @return void
     */
    private function createsMatchesForRound(TournamentRound $round, array $teams, ?string $startingLetter = 'A', ?bool $isLoser = false, ?string $finalMatchName = null): void
    {
        $count = count($teams);

        for ($i = 0; $i < $count; $i++) {
            if ($this->nextRoundIsFinal($isLoser, $count)) {
                $match = new TournamentMatchFinal();
            } else {
                $match = new TournamentMatch();
            }

            $teamOne = $teams[$i];
            $teamTwo = $teams[++$i];

            $match
                ->setName(($isLoser ? 'L' : '') . $startingLetter) // add 'L' if LoserRound
                ->setTeamOne($teamOne)
                ->setTeamTwo($teamTwo)
                ->setRound($round);

            // If one team is a fakeTeam, give win to the other one
            if ($teamTwo instanceof TeamFake) {
                $match
                    ->setResult('3:0')
                    ->setIsFinish(true);
            } else {
                if ($teamOne instanceof TeamFake) {
                    $match
                        ->setResult('0:3')
                        ->setIsFinish(true);
                }
            }

            if ($finalMatchName && $match instanceof TournamentMatchFinal) {
                $match
                    ->setName($finalMatchName)
                    ->setIsGrandFinal(true);
            }

            $this->registry->getManager()->persist($match);

            $startingLetter = $this->utils->getNextLetter($startingLetter);
        }
    }

    /**
     * @param RoundDto $roundDto
     * @param bool $isWinnerRound
     * @param int $teamsRemaining
     * @param int|null $previousCountRound
     * @return TournamentRound
     */
    private function populateRound(RoundDto $roundDto, bool $isWinnerRound, int $teamsRemaining, ?int $previousCountRound = null): TournamentRound
    {
        $round = new TournamentRound();
        $round
            ->setInfos($roundDto->getInfos())
            ->setBestOf($roundDto->getBestOf())
            ->setIsFinish(false)
            ->setCreatedAt(new \DateTime());

        if ($this->nextRoundIsFinal(!$isWinnerRound, $teamsRemaining)) {
            $name = self::FINAL;
        } elseif ($teamsRemaining === 4 && $isWinnerRound) {
            $name = self::SEMI_FINAL;
        } else {
            $roundNumber = $previousCountRound + 1;
            $name = 'Round ' . $roundNumber;
        }

        $roundType = $isWinnerRound ? 'Winner ' : 'Loser ';
        $name = $roundType . $name;

        $round->setName($name);

        return $round;
    }

    /**
     * @param Tournament $tournament
     * @return void
     */
    private function endTournament(Tournament $tournament): void
    {
        $tournament->setIsFinished(true);

        $this->rankingService->assignTournamentPoints($tournament);

        $entityManager = $this->registry->getManager();

        /** @var Team $fakeTeam */
        foreach ($tournament->getTeams() as $fakeTeam) {
            if ($fakeTeam instanceof TeamFake) {
                $tournament->removeTeam($fakeTeam);
                $entityManager->remove($fakeTeam);
            }
        }

        $this->tournament->transitionTo(new FinishedState());

        $this->saveTournament($tournament);
    }

    /**
     * @param TournamentRound $round
     * @return array|array[]
     */
    private function retrieveRoundResults(TournamentRound $round): array
    {
        // Order Matches  by Alphabetic
        $matches = $round->getMatches()->toArray();

        usort($matches, function ($match1, $match2) {
            return strcmp($match1->getName(), $match2->getName());
        });

        $results = [
            self::WINNER_TEAM => [],
            self::LOSER_TEAM => []
        ];

        /** @var TournamentMatch $match */
        foreach ($matches as $match) {
            $results[self::WINNER_TEAM][] = [
                'team' => $match->getWinningTeam(),
                'from_game' => $match->getName(),
            ];
            $results[self::LOSER_TEAM][] = [
                'team' => $match->getLosingTeam(),
                'from_game' => $match->getName(),
            ];
        }

        return $results;
    }

    /**
     * @param array $results
     * @param string $param winner or loser teams needed
     * @return array
     */
    private function getTeamsFromResults(array $results, string $param): array
    {
        $teams = [];
        foreach ($results[$param] as $result) {
            $teams[] = $result['team'];
        }
        return $teams;
    }

    /**
     * @param array $winningTeamsFromLoserRound
     * @param array $losingTeamsFromWinnerRound
     * @return array
     */
    private function getCombinedTeamForLoserRound(array $winningTeamsFromLoserRound, array $losingTeamsFromWinnerRound): array
    {
        $combinedTeams = [];

        // Reverse Array, to get first Loser against last Winner
        $losingTeamsFromWinnerRound = array_reverse($losingTeamsFromWinnerRound);
        $losingCount = count($losingTeamsFromWinnerRound);
        $winningCount = count($winningTeamsFromLoserRound);
        $maxCount = max($losingCount, $winningCount);

        for ($i = 0; $i < $maxCount; $i++) {
            if ($i < $winningCount) {
                $combinedTeams[] = $winningTeamsFromLoserRound[$i];
            }
            if ($i < $losingCount) {
                $combinedTeams[] = $losingTeamsFromWinnerRound[$i];
            }
        }
        return $combinedTeams;
    }

    /**
     * @param array $rounds
     * @return bool
     */
    private function verifyRoundsAreOver(array $rounds): bool
    {
        /** @var TournamentRound $round */
        foreach ($rounds as $round) {
            if (!$round->isFinish()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param bool $isLoser LoserBracket or WinnerBracket
     * @param int $teamCount in round
     * @return bool
     */
    private function nextRoundIsFinal(bool $isLoser, int $teamCount): bool
    {
        $nextRoundIsFinal = false;
        $roundFound = $this->tournamentRoundRepository->findRoundsWithOneMatchFromBracket(BracketEnum::LOSER_BRACKET->value);

        // If LoserRound, Final is not the first Round with two teams. Second is.
        // If WinnerRound, Final is the first Round with two teams.
        if ($isLoser && $roundFound && $teamCount === 2) {
            $nextRoundIsFinal = true;
        } elseif (!$isLoser && $teamCount === 2) {
            $nextRoundIsFinal = true;
        }

        return $nextRoundIsFinal;
    }
}