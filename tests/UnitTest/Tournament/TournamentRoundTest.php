<?php

namespace App\Tests\UnitTest\Tournament;

use App\Entity\Tournament\TournamentBracket;
use App\Entity\Tournament\TournamentMatch;
use App\Entity\Tournament\TournamentRound;
use PHPUnit\Framework\TestCase;

class TournamentRoundTest extends TestCase
{
    private TournamentRound $round;
    private TournamentMatch $match;

    /**
     * Set up a new TournamentRound object with a TournamentMatch for each test.
     */
    protected function setUp(): void
    {
        $this->round = new TournamentRound();
        $this->match = new TournamentMatch();
        $this->round->addMatch($this->match);
    }

    /**
     * Test if verifyMatchesAreOver returns true when all matches are finished
     * and none are waiting for admin approval.
     */
    public function testVerifyMatchesAreOverWhenMatchesAreFinishedAndNotWaitingForAdmin()
    {
        $this->match
            ->setIsFinish(true)
            ->setIsWaitingForAdmin(false);

        $this->assertTrue($this->round->verifyMatchesAreOver(), 'Expected all matches to be over');
    }

    /**
     * Test if verifyMatchesAreOver returns false when some matches are not finished.
     */
    public function testVerifyMatchesAreOverWhenMatchesAreNotFinished()
    {
        $this->match->setIsFinish(false);

        $this->assertFalse($this->round->verifyMatchesAreOver(), 'Expected some matches to not be over');
    }

    /**
     * Test if verifyMatchesAreOver returns false when some matches are finished
     * but are waiting for admin approval.
     */
    public function testVerifyMatchesAreOverWhenMatchesAreWaitingForAdmin()
    {
        $this->match
            ->setIsFinish(true)
            ->setIsWaitingForAdmin(true);

        $this->assertFalse($this->round->verifyMatchesAreOver(), 'Expected some matches to be waiting for admin');
    }

    public function testGettersAndSetters()
    {
        // Test name
        $this->round->setName('Round Name');
        $this->assertEquals('Round Name', $this->round->getName());

        // Test bestOf
        $this->round->setBestOf(5);
        $this->assertEquals(5, $this->round->getBestOf());

        // Test bracket
        $bracket = new TournamentBracket();
        $this->round->setBracket($bracket);
        $this->assertEquals($bracket, $this->round->getBracket());

        // Test match
        $this->round->addMatch($this->match);
        $this->assertContains($this->match, $this->round->getMatches());
        $this->round->removeMatch($this->match);
        $this->assertNotContains($this->match, $this->round->getMatches());

        // Test info
        $this->round->setInfos(['infos']);
        $this->assertEquals(['infos'], $this->round->getInfos());

        // Test createdAt
        $createdAt = new \DateTime();
        $this->round->setCreatedAt($createdAt);
        $this->assertEquals($createdAt, $this->round->getCreatedAt());

        // Test isFinish
        $this->round->setIsFinish(true);
        $this->assertTrue($this->round->isFinish());
    }
}