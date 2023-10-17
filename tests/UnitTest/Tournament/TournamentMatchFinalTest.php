<?php

namespace App\Tests\UnitTest\Tournament;

use App\Entity\Tournament\TournamentMatchFinal;
use PHPUnit\Framework\TestCase;

class TournamentMatchFinalTest extends TestCase
{
    private TournamentMatchFinal $tournamentMatchFinal;

    /**
     * Set up a new TournamentMatchFinal object for each test.
     */
    protected function setUp(): void
    {
        $this->tournamentMatchFinal = new TournamentMatchFinal();
    }

    public function testGettersAndSetters()
    {
        // Test isGrandFinal
        $this->tournamentMatchFinal->setIsGrandFinal(true);
        $this->assertTrue($this->tournamentMatchFinal->getIsGrandFinal());

        // Test requiresReplay
        $this->tournamentMatchFinal->setRequiresReplay(true);
        $this->assertTrue($this->tournamentMatchFinal->isRequiresReplay());

        // Test setting isGrandFinal to false
        $this->tournamentMatchFinal->setIsGrandFinal(false);
        $this->assertFalse($this->tournamentMatchFinal->getIsGrandFinal());

        // Test setting requiresReplay to false
        $this->tournamentMatchFinal->setRequiresReplay(false);
        $this->assertFalse($this->tournamentMatchFinal->isRequiresReplay());
    }

}