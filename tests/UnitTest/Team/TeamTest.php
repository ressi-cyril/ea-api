<?php

namespace App\Tests\UnitTest\Team;

use App\Entity\Team\Team;
use App\Entity\Team\TeamInvite;
use App\Entity\Tournament\Tournament;
use App\Entity\User\PlayerUser;
use App\Tournament\State\InitialState;
use PHPUnit\Framework\TestCase;

class TeamTest extends TestCase
{
    private Team $team;

    /**
     * Set up a new Team object for each test.
     */
    protected function setUp(): void
    {
        $this->team = new Team();
    }

    public function testIsTeamPlayer()
    {
        // This player should be considered a "team player" by the method we are testing
        $player1 = new PlayerUser();
        $this->team->addPlayer($player1);

        // This player should NOT be considered a "team player" by the method we are testing
        $player2 = new PlayerUser();

        // Verify if the players are in the team
        $this->assertTrue($this->team->isTeamPlayer($player1), 'Expected player1 to be a team player');
        $this->assertFalse($this->team->isTeamPlayer($player2), 'Expected player2 not to be a team player');
    }

    public function testGettersAndSetters()
    {
        // Test name
        $this->team->setName('Team Name');
        $this->assertEquals('Team Name', $this->team->getName());

        // Test points
        $this->team->setPoints(100);
        $this->assertEquals(100, $this->team->getPoints());

        // Test type
        $this->team->setType('Type');
        $this->assertEquals('Type', $this->team->getType());

        // Test createdAt
        $createdAt = new \DateTime();
        $this->team->setCreatedAt($createdAt);
        $this->assertEquals($createdAt, $this->team->getCreatedAt());

        // Test captain
        $captain = new PlayerUser();
        $this->team->setCaptain($captain);
        $this->assertEquals($captain, $this->team->getCaptain());

        // Test players (add and remove)
        $player = new PlayerUser();
        $this->team->addPlayer($player);
        $this->assertContains($player, $this->team->getPlayers());
        $this->team->removePlayer($player);
        $this->assertNotContains($player, $this->team->getPlayers());

        // Test tournaments (add and remove)
        $tournament = new Tournament(new InitialState());
        $this->team->addTournament($tournament);
        $this->assertContains($tournament, $this->team->getTournaments());
        $this->team->removeTournament($tournament);
        $this->assertNotContains($tournament, $this->team->getTournaments());

        // Test invites (add and remove)
        $invite = new TeamInvite();
        $this->team->addInvite($invite);
        $this->assertContains($invite, $this->team->getInvites());
        $this->team->removeInvite($invite);
        $this->assertNotContains($invite, $this->team->getInvites());
    }

}