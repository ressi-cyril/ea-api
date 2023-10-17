<?php

namespace App\Tests\UnitTest\Tournament;

use App\Entity\Team\Team;
use App\Entity\Tournament\TournamentMatch;
use App\Entity\Tournament\TournamentRound;
use PHPUnit\Framework\TestCase;

class TournamentMatchTest extends TestCase
{
    private TournamentMatch $tournamentMatch;

    /**
     * Set up a new TournamentMatch object for each test.
     */
    protected function setUp(): void
    {
        $this->tournamentMatch = new TournamentMatch();
    }

    public function testGettersAndSetters()
    {
        // Test Name
        $this->tournamentMatch->setName('Match1');
        $this->assertEquals('Match1', $this->tournamentMatch->getName());

        // Test TeamOne
        $teamOne = new Team();
        $this->tournamentMatch->setTeamOne($teamOne);
        $this->assertEquals($teamOne, $this->tournamentMatch->getTeamOne());

        // Test TeamTwo
        $teamTwo = new Team();
        $this->tournamentMatch->setTeamTwo($teamTwo);
        $this->assertEquals($teamTwo, $this->tournamentMatch->getTeamTwo());

        // Test Result
        $this->tournamentMatch->setResult('2-1');
        $this->assertEquals('2-1', $this->tournamentMatch->getResult());

        // Test IsFinish
        $this->tournamentMatch->setIsFinish(true);
        $this->assertTrue($this->tournamentMatch->isFinish());

        // Test IsWaitingForAdmin
        $this->tournamentMatch->setIsWaitingForAdmin(true);
        $this->assertTrue($this->tournamentMatch->isWaitingForAdmin());

        // Test Round
        $round = new TournamentRound();
        $this->tournamentMatch->setRound($round);
        $this->assertEquals($round, $this->tournamentMatch->getRound());

        // Test WinningTeam and LosingTeam
        $this->assertEquals($teamOne, $this->tournamentMatch->getWinningTeam());
        $this->assertEquals($teamTwo, $this->tournamentMatch->getLosingTeam());
    }

}