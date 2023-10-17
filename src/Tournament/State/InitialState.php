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
use App\Model\Team\Enum\TeamEnum;
use App\Repository\Tournament\TournamentRoundRepository;
use App\Service\Ranking\TournamentRankingService;
use App\Service\UtilsService;
use App\Service\ValidationService;
use App\Tournament\Dto\TournamentDto;
use App\Tournament\Enum\TournamentEnum;
use App\Tournament\Enum\TournamentStatesEnum;
use App\Tournament\Interface\TournamentStateInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class InitialState implements TournamentStateInterface
{
    const FINAL = 'Final';
    const SEMI_FINAL = 'Semi Final';
    private ?TournamentRankingService $rankingService = null;
    private ValidationService $validationService;
    private ManagerRegistry $registry;
    private Tournament $tournament;
    private TournamentRoundRepository $tournamentRoundRepository;
    private ?UtilsService $utils = null;

    /**
     * {@inheritdoc}
     */
    public function initializeTournament(?TournamentDto $tournamentDto = null): Tournament
    {
        if ($tournamentDto === null) {
            throw new BadRequestHttpException('TournamentDto is missing');
        }

        $this->validationService->performEntityValidation($tournamentDto, ["tournamentCreate"]);

        if (!in_array($tournamentDto->type, TournamentEnum::getValidTypes())) {
            throw new BadRequestHttpException('Tournament type is invalid', null, 400);
        }

        $tournament = $this->getTournament();

        $this->populateTournament($tournament, $tournamentDto);

        $this->rankingService->initializeRanking($tournament, $tournamentDto->getPointsByTier());

        $this->validateAndSaveTournament($tournament, ["tournamentCreate"]);

        return $tournament;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTournament(?TournamentDto $tournamentDto = null): Tournament
    {
        $tournament = $this->getTournament();

        $this->validationService->performEntityValidation($tournamentDto, ["tournamentUpdate"]);

        if (!in_array($tournamentDto->type, TournamentEnum::getValidTypes())) {
            throw new BadRequestHttpException('Tournament type is invalid', null, 400);
        }

        $this->populateTournament($tournament, $tournamentDto, true);

        $this->rankingService->updateRanking($tournament, $tournamentDto->getPointsByTier());

        $this->validateAndSaveTournament($tournament, ["tournamentUpdate"]);

        return $tournament;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTournament(): void
    {
        $entityManager = $this->registry->getManager();
        $entityManager->remove($this->getTournament());
        $entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function teamJoinOrLeaveTournament(?Team $team = null, ?bool $isJoining = true): void
    {
        $tournament = $this->getTournament();
        $teams = $tournament->getTeams()->toArray();
        $groups = [];

        if ($isJoining) {
            if (in_array($team, $teams)) {
                throw new BadRequestHttpException('Team is already participating in this tournament');
            }

            if ($tournament->getType() !== $team->getType()) {
                throw new BadRequestHttpException("Team is not allowed to join tournament because type don't match");
            }

            $tournament->addTeam($team);
            $groups[] = 'tournamentJoin';
        } else {
            if (!in_array($team, $teams)) {
                throw new BadRequestHttpException('Team is not participating in this tournament');
            }

            $tournament->removeTeam($team);
            $team->removeTournament($tournament);

            $groups[] = 'tournamentLeave';
        }

        $this->validateAndSaveTournament($tournament, $groups);
    }

    /**
     * {@inheritdoc}
     */
    public function startTournament(?RoundDto $roundDto = null): Tournament
    {
        $tournament = $this->getTournament();

        $this->validateTournamentReadyToStart($tournament);

        $this->validationService->performEntityValidation($roundDto, ['tournamentGenerate']);

        $tournament->setIsStarted(true);

        $tournament = $this->generateBrackets($tournament, $roundDto);

        $this->validationService->performEntityValidation($tournament, ['tournamentGenerate']);

        $this->tournament->transitionTo(new InProgressState());

        $this->saveTournament($tournament);

        return $tournament;
    }

    /**
     * {@inheritdoc}, not available in this state
     */
    public function continueTournament(?RoundDto $roundDto = null): void
    {
        throw new BadRequestHttpException('Invalid operation: "continueTournament" is not allowed in "InitialState" state.');
    }

    /**
     * {@inheritdoc}, not available in this state
     */
    public function endRound(?TournamentRound $round = null): void
    {
        throw new BadRequestHttpException('Invalid operation: "endRound" is not allowed in "InitialState" state.');
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
    public function getTournament(): Tournament
    {
        return $this->tournament;
    }

    /**
     * {@inheritdoc}
     */
    public function getStateEnum(): TournamentStatesEnum
    {
        return TournamentStatesEnum::INITIAL;
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
        $this->rankingService = $rankingService;
        $this->validationService = $validationService;
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
     * Populate Tournament with TournamentDto Data.
     *
     * @param Tournament $tournament
     * @param TournamentDto $tournamentDto
     * @param bool|null $isUpdating
     * @return void
     */
    private function populateTournament(Tournament $tournament, TournamentDto $tournamentDto, ?bool $isUpdating = false): void
    {
        $tournament
            ->setName($tournamentDto->name)
            ->setPoints($tournamentDto->points)
            ->setMaxTeams($tournamentDto->maxTeams)
            ->setHasLoserBracket($tournamentDto->hasLoserBracket)
            ->setCashPrice($tournamentDto->cashPrice)
            ->setType($tournamentDto->type)
            ->setStartAt($tournamentDto->startAt);

        // when creating tournament, set isStarted and isFinished
        if (!$isUpdating) {
            $tournament
                ->setCreatedAt(new \DateTime())
                ->setIsStarted(false)
                ->setIsFinished(false);
        }

        if (UtilsService::isPowerOfTwo($tournament->getMaxTeams()) === false) {
            throw new BadRequestHttpException('Maximum amount of Team must be power of 2.');
        }
    }

    /**
     * Generate Brackets, Rounds and Matches needed
     *
     * @param Tournament $tournament
     * @param RoundDto $roundDto
     * @return Tournament
     */
    private function generateBrackets(Tournament $tournament, RoundDto $roundDto): Tournament
    {
        // Create a winner bracket and add it to tournament
        $winnerBracket = new TournamentBracket();
        $winnerBracket->setName(BracketEnum::WINNER_BRACKET->value);
        $tournament->addTournamentBracket($winnerBracket);

        // Create a loser bracket if needed
        if ($tournament->hasLoserBracket()) {
            $loserBracket = new TournamentBracket();
            $loserBracket->setName(BracketEnum::LOSER_BRACKET->value);
            $tournament->addTournamentBracket($loserBracket);
        }

        // Generate fakeTeam if needed
        $this->generateFakeTeams(count($tournament->getTeams()), $tournament);

        // Create first round of winnerBracket
        $round = $this->populateRound($roundDto, true, count($tournament->getTeams()));
        $winnerBracket->addTournamentRound($round);

        // Get ordered teams corresponding to needed pattern
        $teams = $this->orderTeamsForFirstRound($tournament->getTeams()->toArray());

        // Pair teams to matches
        $this->createsMatchesForRound($round, $teams);

        return $tournament;
    }

    /**
     * Algorithm that will result in the top seed playing the lowest seed,
     * the 2nd seed playing the 2nd-lowest seed, the 3rd seed playing the 3rd-lowest seed, etc.
     *
     * @param array $teams
     * @return array $teams
     */
    private function orderTeamsForFirstRound(array $teams): array
    {
        $count = count($teams);
        $numberOfRounds = log($count / 2, 2);

        usort($teams, function ($a, $b) {
            return $b->getPoints() - $a->getPoints();
        });

        for ($i = 0; $i < $numberOfRounds; $i++) {
            $out = array();
            $splice = pow(2, $i);

            while (count($teams) > 0) {
                $out = array_merge($out, array_splice($teams, 0, $splice));
                $out = array_merge($out, array_splice($teams, -$splice));
            }
            $teams = $out;
        }

        return $teams;
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
     * Generate FakeTeams, if count of tournament's Teams is not a power of 2.
     *
     * @param int $actualTeamCount
     * @param Tournament $tournament
     * @return void
     */
    private function generateFakeTeams(int $actualTeamCount, Tournament $tournament): void
    {
        $teamsNeeded = 1;
        while ($teamsNeeded < $actualTeamCount) {
            $teamsNeeded *= 2;
        }

        $fakeTeamsNeeded = $teamsNeeded - $actualTeamCount;

        for ($i = 0; $i < $fakeTeamsNeeded; $i++) {
            $fakeTeam = new TeamFake();
            $fakeTeam
                ->setType(TeamEnum::ONE->value)
                ->setName(TeamFake::FAKE_NAME)
                ->setCreatedAt(new \DateTime())
                ->setPoints(-10);

            $tournament->addTeam($fakeTeam);

            $this->registry->getManager()->persist($fakeTeam);
        }
    }

    /**
     * Checks if the tournament is valid to start.
     *
     * @param Tournament $tournament
     * @throws BadRequestHttpException
     */
    private function validateTournamentReadyToStart(Tournament $tournament): void
    {
        $errors = [];

        if (new \DateTime('now') < $tournament->getStartAt()) {
            $errors[] = 'The tournament cannot start until the scheduled time: ' . $tournament->getStartAt()->format(
                    'd F Y, H:i'
                );
        }

        if ($tournament->isStarted()) {
            $errors[] = 'Tournament has already started';
        }

        if ($tournament->isFinished()) {
            $errors[] = 'Tournament is already finished';
        }

        if ($errors) {
            throw new BadRequestHttpException(implode(', ', $errors));
        }

        $this->isTeamCountCompatibleWithMaxTeams($tournament->getMaxTeams(), count($tournament->getTeams()));
    }

    /**
     * @param int $maxTeams
     * @param int $teamCount
     * @return void
     */
    private function isTeamCountCompatibleWithMaxTeams(int $maxTeams, int $teamCount): void
    {
        // Round $teamCount to next powerOf2
        $logBaseTwo = log($teamCount, 2);
        $nextPowerOfTwo = ceil($logBaseTwo);
        $roundedTeamCount = pow(2, $nextPowerOfTwo);

        // Verify if $maxTeams and $roundedTeamCount are same powerOf2
        $logMaxTeams = log($maxTeams, 2);
        $logRoundedTeamCount = log($roundedTeamCount, 2);

        $isCompatible = floor($logMaxTeams) === $logMaxTeams && floor($logRoundedTeamCount) === $logRoundedTeamCount && $logMaxTeams === $logRoundedTeamCount;

        if (!$isCompatible) {
            throw new BadRequestHttpException("Tournament's maxTeams must be equal to:" . $roundedTeamCount . ". Tournament's maxTeams must be updated");
        }
    }

    /**
     * Validation and save to database.
     *
     * @param Tournament $tournament
     * @param array|null $groups
     * @return void
     */
    private function validateAndSaveTournament(Tournament $tournament, ?array $groups = null): void
    {
        // Verify entity validation
        $this->validationService->performEntityValidation($tournament, $groups);

        // Save tournament to Database
        $this->saveTournament($tournament);
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