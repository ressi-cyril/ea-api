<?php

namespace App\Tests\UnitTest\Tournament;

use App\Entity\Tournament\Tournament;
use App\Entity\Tournament\TournamentRanking;
use App\Tournament\State\InitialState;
use PHPUnit\Framework\TestCase;

class TournamentRankingTest extends TestCase
{
    private TournamentRanking $tournamentRanking;

    /**
     * Set up a new TournamentRanking object for each test.
     */
    protected function setUp(): void
    {
        $this->tournamentRanking = new TournamentRanking();
    }

    public function testGettersAndSetters(): void
    {
        // Test Tournament
        $tournament = new Tournament(new InitialState());
        $this->tournamentRanking->setTournament($tournament);
        $this->assertSame($tournament, $this->tournamentRanking->getTournament());

        // Test PointsByTier
        $pointsByTier = ['tier1' => 100, 'tier2' => 50];
        $this->tournamentRanking->setPointsByTier($pointsByTier);
        $this->assertSame($pointsByTier, $this->tournamentRanking->getPointsByTier());

        // Test Result
        $result = ['team1' => 100, 'team2' => 50];
        $this->tournamentRanking->setResult($result);
        $this->assertSame($result, $this->tournamentRanking->getResult());
    }

}