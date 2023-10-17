<?php

namespace App\Tests\UnitTest\User;

use App\Entity\Team\Team;
use App\Entity\Team\TeamInvite;
use App\Entity\User\PlayerUser;
use PHPUnit\Framework\TestCase;

class PlayerUserTest extends TestCase
{
    private PlayerUser $playerUser;

    /**
     * Set up a new PlayerUser object for each test.
     */
    protected function setUp(): void
    {
        $this->playerUser = new PlayerUser();
    }

    public function testGettersAndSetters(): void
    {
        // Test GamerTag
        $this->playerUser->setGamerTag('Gamer123');
        $this->assertEquals('Gamer123', $this->playerUser->getGamerTag());

        // Test Points
        $this->playerUser->setPoints(100);
        $this->assertEquals(100, $this->playerUser->getPoints());

        // Test IsCaptain
        $this->playerUser->setIsCaptain(true);
        $this->assertTrue($this->playerUser->IsCaptain());

        // Test Teams
        $team = new Team();
        $this->playerUser->addTeam($team);
        $this->assertContains($team, $this->playerUser->getTeams());
        $this->playerUser->removeTeam($team);
        $this->assertNotContains($team, $this->playerUser->getTeams());

        // Test TeamInvites
        $teamInvite = new TeamInvite();
        $this->playerUser->addTeamInvite($teamInvite);
        $this->assertContains($teamInvite, $this->playerUser->getTeamInvites());
        $this->playerUser->removeTeamInvite($teamInvite);
        $this->assertNotContains($teamInvite, $this->playerUser->getTeamInvites());
    }

}
