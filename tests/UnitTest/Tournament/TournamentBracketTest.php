<?php

namespace App\Tests\UnitTest\Tournament;

use App\Entity\Tournament\Tournament;
use App\Entity\Tournament\TournamentBracket;
use App\Entity\Tournament\TournamentRound;
use App\Tournament\State\InitialState;
use PHPUnit\Framework\TestCase;

class TournamentBracketTest extends TestCase
{
    private TournamentBracket $tournamentBracket;

    /**
     * Set up a new TournamentBracket object  for each test.
     */
    protected function setUp(): void
    {
        $this->tournamentBracket = new TournamentBracket();
    }

    public function testGettersAndSetters(): void
    {
        // Test Name
        $this->tournamentBracket->setName('Bracket1');
        $this->assertEquals('Bracket1', $this->tournamentBracket->getName());

        // Test Tournament
        $tournament = new Tournament(new InitialState());
        $this->tournamentBracket->setTournament($tournament);
        $this->assertEquals($tournament, $this->tournamentBracket->getTournament());

        // Test TournamentRounds
        $tournamentRound1 = new TournamentRound();
        $tournamentRound2 = new TournamentRound();

        $this->tournamentBracket->addTournamentRound($tournamentRound1);
        $this->tournamentBracket->addTournamentRound($tournamentRound2);

        $this->assertCount(2, $this->tournamentBracket->getTournamentRounds());
        $this->assertTrue($this->tournamentBracket->getTournamentRounds()->contains($tournamentRound1));
        $this->assertTrue($this->tournamentBracket->getTournamentRounds()->contains($tournamentRound2));

        // Test removing a TournamentRound
        $this->tournamentBracket->removeTournamentRound($tournamentRound1);
        $this->assertCount(1, $this->tournamentBracket->getTournamentRounds());
        $this->assertFalse($this->tournamentBracket->getTournamentRounds()->contains($tournamentRound1));
    }

}