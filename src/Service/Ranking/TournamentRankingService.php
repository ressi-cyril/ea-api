<?php

namespace App\Service\Ranking;

use App\Entity\Team\Team;
use App\Entity\Tournament\Tournament;
use App\Entity\Tournament\TournamentRanking;
use App\Repository\Team\TeamRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TournamentRankingService
{
    private ManagerRegistry $registry;
    private TeamRepository $teamRepository;

    public function __construct(ManagerRegistry $registry, TeamRepository $teamRepository)
    {
        $this->registry = $registry;
        $this->teamRepository = $teamRepository;
    }

    /**
     * Initialize the ranking for a tournament.
     *
     * @param Tournament $tournament The tournament entity.
     * @param array $pointsByTier Points to be awarded for each tier.
     */
    public function initializeRanking(Tournament $tournament, array $pointsByTier): void
    {
        $countTeamsByTier = $this->getAmountOfTeamsByTier($tournament->getMaxTeams(), $tournament->hasLoserBracket());
        $this->verifyIsValidOrThrow($pointsByTier, $countTeamsByTier, $tournament->getPoints());

        $ranking = new TournamentRanking();
        $ranking
            ->setPointsByTier($pointsByTier)
            ->setTournament($tournament);

        $tournament->setRanking($ranking);

        $this->registry->getManager()->persist($ranking);
    }

    /**
     * Update the ranking for a tournament
     * @param Tournament $tournament
     * @param array $pointsByTier
     * @return void
     */
    public function updateRanking(Tournament $tournament, array $pointsByTier): void
    {
        $countTeamsByTier = $this->getAmountOfTeamsByTier($tournament->getMaxTeams(), $tournament->hasLoserBracket());
        $this->verifyIsValidOrThrow($pointsByTier, $countTeamsByTier, $tournament->getPoints());

        $ranking = $tournament->getRanking();
        $ranking
            ->setPointsByTier($pointsByTier)
            ->setTournament($tournament);

        $this->registry->getManager()->persist($ranking);
    }

    /**
     * Assign teams to their respective tiers based on their results.
     *
     * @param Tournament $tournament The tournament ranking entity.
     * @param array $teams The teams to be assigned.
     */
    public function assignTeamsToTiers(Tournament $tournament, array $teams): void
    {
        $tournamentRanking = $tournament->getRanking();
        $countTeamsByTier = $this->getAmountOfTeamsByTier($tournament->getMaxTeams(), $tournament->hasLoserBracket());
        $results = $tournamentRanking->getResult() ?? [];

        // Loop through the tiers in reverse order
        foreach (array_reverse($countTeamsByTier, true) as $tier => $count) {
            if (count($teams) == 0) {
                $results[$tier] = $results[$tier] ?? null;
            } else {
                // Calculate the number of teams needed for this tier
                $existingTeams = count($results[$tier] ?? []);
                $neededTeams = $count - $existingTeams;

                // If more teams are needed for this tier, add them
                if ($neededTeams > 0) {
                    $teamsToAssign = array_splice($teams, 0, min($neededTeams, count($teams)));

                    /** @var Team $team */
                    foreach ($teamsToAssign as $team) {
                        $results[$tier][] = $team->getName();
                    }
                }
            }
        }

        // Update the results in the TournamentRanking entity
        $tournamentRanking->setResult($results);
    }

    /**
     * @param Tournament $tournament
     * @return void
     */
    public function assignTournamentPoints(Tournament $tournament): void
    {
        $ranking = $tournament->getRanking();
        $pointsByTier = $ranking->getPointsByTier();

        foreach ($ranking->getResult() as $tier => $teams) {
            $points = $pointsByTier[$tier - 1];
            if ($teams !== null) {
                foreach ($teams as $team) {
                    $teamFound = $this->teamRepository->findOneBy(['name' => $team]);
                    if ($teamFound) {
                        $teamFound->addPoints($points);
                    }
                }
            }
        }
    }

    /**
     * Calculate the total number of teams required for each tier based on the maximum number of teams
     */
    private function getAmountOfTeamsByTier(int $maxTeams, ?bool $hasLoserBracket = true): array
    {
        if ($hasLoserBracket) {
            // Logic with LoserBracket
            // If the number of teams is 4 or less, set the total number of tiers
            // to be equal to the number of teams
            if ($maxTeams <= 4) {
                $totalTiers = $maxTeams;
            } else {
                // Calculate the power of 2 needed for the number of teams
                $powerOfTwo = log($maxTeams, 2);
                // Calculate the total number of tiers needed for the tournament
                $totalTiers = 2 * $powerOfTwo;
            }
        } else {
            // Logic without Loser Bracket
            if ($maxTeams <= 2) {
                $totalTiers = $maxTeams;
            } else {
                $powerOfTwo = log($maxTeams, 2);
                $totalTiers = $powerOfTwo + 1;
            }
        }

        // Initialize an array to hold the number of teams per tier
        $teamsPerTiers = [];
        if ($hasLoserBracket) {
            // Logic with Loser Bracket
            // For the first 4 tiers (or fewer, depending on totalTiers),
            // there is only 1 team per tier
            for ($i = 1; $i <= min(4, $totalTiers); $i++) {
                $teamsPerTiers[$i] = 1;
            }

            // If the number of teams is greater than 4, continue to populate the array
            if ($maxTeams > 4) {
                // Initialize the number of teams for the 5th tier
                $teams = 2;
                // Calculate the number of teams for each subsequent tier
                for ($i = 5; $i <= $totalTiers; $i++) {
                    $teamsPerTiers[$i] = $teams;
                    // Double the number of teams every even tier
                    if ($i % 2 == 0) {
                        $teams *= 2;
                    }
                }
            }
        } else {
            // Logic without Loser Bracket
            // For the first 2 tiers (or fewer, depending on totalTiers),
            // there is only 1 team per tier
            for ($i = 1; $i <= min(2, $totalTiers); $i++) {
                $teamsPerTiers[$i] = 1;
            }

            // If the number of teams is greater than 2, continue to populate the array
            if ($maxTeams > 2) {
                // Initialize the number of teams for the 3rd tier
                $teams = 2;
                // Calculate the number of teams for each subsequent tier
                for ($i = 3; $i <= $totalTiers; $i++) {
                    $teamsPerTiers[$i] = $teams;
                    // Double the number of teams every tier
                    $teams *= 2;
                }
            }
        }

        return $teamsPerTiers;
    }

    /**
     * Verify if the provided points and tiers are valid
     *
     * @param array $pointsByTier
     * @param array $teamsByTier
     * @param int $totalPointsToEarn
     * @return void
     */
    private function verifyIsValidOrThrow(array $pointsByTier, array $teamsByTier, int $totalPointsToEarn): void
    {
        if (count($pointsByTier) !== count($teamsByTier)) {
            throw new BadRequestHttpException('Invalid "points_by_tier" count, ' . count($teamsByTier) . ' expected');
        }

        $pointsGiven = 0;
        foreach ($pointsByTier as $tierPoint) {
            if (!is_int($tierPoint)) {
                throw new BadRequestHttpException("points by tier must be integer type");
            }
            $pointsGiven += $tierPoint;
        }

        if ($pointsGiven !== $totalPointsToEarn) {
            throw new BadRequestHttpException("Incorrect ranking's total points, " . $totalPointsToEarn . ' excepted');
        }
    }
}