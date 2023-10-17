<?php

namespace App\Tests\UnitTest\Tournament;

use App\Entity\Team\Team;
use App\Entity\Tournament\Tournament;
use App\Entity\Tournament\TournamentBracket;
use App\Entity\Tournament\TournamentRanking;
use App\Tournament\Enum\TournamentEnum;
use App\Tournament\State\FinishedState;
use App\Tournament\State\InitialState;
use App\Tournament\State\InProgressState;
use PHPUnit\Framework\TestCase;

class TournamentTest extends TestCase
{
    private Tournament $tournament;

    /**
     * Set up a new Tournament object with an initial state for each test.
     */
    protected function setUp(): void
    {
        $this->tournament = new Tournament(new InitialState());
    }

    /**
     * Test if the constructor correctly initializes the Tournament object
     * with the given initial state.
     */
    public function testConstructor()
    {
        $initialState = new InitialState();
        $tournament = new Tournament($initialState);

        // Assert that the state is correctly set in the Tournament object
        $this->assertSame($tournament->getState(), $initialState);

        // Assert that the state string matches the enum value of the initial state
        $this->assertSame($tournament->getStateString(), $initialState->getStateEnum());

        // Assert that the Tournament object is correctly set in the state
        $this->assertEquals($tournament, $initialState->getTournament());
    }

    /**
     * Test if the state transition method correctly updates the state
     * and the associated state string.
     */
    public function testStateTransition()
    {
        $inProgressState = new InProgressState();
        $this->tournament->transitionTo($inProgressState);

        // Assert that the state is correctly updated in the Tournament object
        $this->assertSame($this->tournament->getState(), $inProgressState);

        // Assert that the state string matches the enum value of the new state
        $this->assertSame($this->tournament->getStateString(), $inProgressState->getStateEnum());

        // Assert that the Tournament object is correctly set in the new state
        $this->assertEquals($this->tournament, $inProgressState->getTournament());
    }

    /**
     * Test if the initializeState method correctly sets the state to InitialState.
     * This method is crucial because the state, being an interface, is not saved in the database.
     * When a Tournament is retrieved in a controller, it doesn't have a state.
     * However, it does have a StateString (TournamentStateEnum), and using that,
     * the initializeState method creates and sets the corresponding state.
     */
    public function testInitializeStateWithInitialState()
    {
        $initialState = new InitialState();
        $this->tournament->transitionTo($initialState);

        $initializedState = $this->tournament->initializeState();

        // Assert that the initialized state is an instance of InitialState
        $this->assertInstanceOf(InitialState::class, $initializedState);

        // Assert that the state is correctly set in the Tournament object
        $this->assertEquals($this->tournament->getState(), $initializedState);

        // Assert that the Tournament object is correctly set in the state
        $this->assertEquals($this->tournament, $initializedState->getTournament());
    }

    /**
     * Test if the initializeState method correctly sets the state to InProgressState.
     */
    public function testInitializeStateWithInProgressState()
    {
        $inProgressState = new InProgressState();
        $this->tournament->transitionTo($inProgressState);

        $initializedState = $this->tournament->initializeState();

        // Assert that the initialized state is an instance of InProgressState
        $this->assertInstanceOf(InProgressState::class, $initializedState);

        // Assert that the state is correctly set in the Tournament object
        $this->assertEquals($this->tournament->getState(), $initializedState);

        // Assert that the Tournament object is correctly set in the state
        $this->assertEquals($this->tournament, $initializedState->getTournament());
    }

    /**
     * Test if the initializeState method correctly sets the state to FinishedState.
     */
    public function testInitializeStateWithFinishedState()
    {
        $finishedState = new FinishedState();
        $this->tournament->transitionTo($finishedState);

        $initializedState = $this->tournament->initializeState();

        // Assert that the initialized state is an instance of FinishedState
        $this->assertInstanceOf(FinishedState::class, $initializedState);

        // Assert that the state is correctly set in the Tournament object
        $this->assertEquals($this->tournament->getState(), $initializedState);

        // Assert that the Tournament object is correctly set in the state
        $this->assertEquals($this->tournament, $initializedState->getTournament());
    }

    public function testGettersAndSetters()
    {
        $tournamentBracket = new TournamentBracket();
        $team = new Team();
        $ranking = new TournamentRanking();

        // Test name
        $this->tournament->setName('Test Tournament');
        $this->assertEquals('Test Tournament', $this->tournament->getName());

        // Test points
        $this->tournament->setPoints(100);
        $this->assertEquals(100, $this->tournament->getPoints());

        // Test maxTeams
        $this->tournament->setMaxTeams(16);
        $this->assertEquals(16, $this->tournament->getMaxTeams());

        // Test hasLoserBracket
        $this->tournament->setHasLoserBracket(true);
        $this->assertTrue($this->tournament->hasLoserBracket());

        // Test type
        $this->tournament->setType(TournamentEnum::EIGHT->value);
        $this->assertEquals(TournamentEnum::EIGHT->value, $this->tournament->getType());

        // Test cashPrice
        $this->tournament->setCashPrice('1000.00');
        $this->assertEquals('1000.00', $this->tournament->getCashPrice());

        // Test Teams (addTeam, GetTeam and removeTeam)
        $this->tournament->addTeam($team);
        $this->assertContains($team, $this->tournament->getTeams());

        $this->tournament->removeTeam($team);
        $this->assertNotContains($team, $this->tournament->getTeams());

        // Test createdAt
        $date = new \DateTime();
        $this->tournament->setCreatedAt($date);
        $this->assertEquals($date, $this->tournament->getCreatedAt());

        // Test startAt
        $this->tournament->setStartAt($date);
        $this->assertEquals($date, $this->tournament->getStartAt());

        // Test isStarted
        $this->tournament->setIsStarted(true);
        $this->assertTrue($this->tournament->isStarted());

        // Test isFinished
        $this->tournament->setIsFinished(true);
        $this->assertTrue($this->tournament->isFinished());

        // Test TournamentBrackets (addTournamentBracket and getTournamentBrackets)
        $this->tournament->addTournamentBracket($tournamentBracket);
        $this->assertContains($tournamentBracket, $this->tournament->getTournamentBrackets());

        // Test Ranking
        $this->tournament->setRanking($ranking);
        $this->assertEquals($ranking, $this->tournament->getRanking());
    }
}