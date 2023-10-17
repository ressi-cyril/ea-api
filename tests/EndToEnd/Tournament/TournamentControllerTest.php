<?php

namespace App\Tests\EndToEnd\Tournament;

use App\Entity\Team\Team;
use App\Entity\Tournament\Tournament;
use App\Entity\User\User;
use DateTimeInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TournamentControllerTest extends WebTestCase
{
    private static ?KernelBrowser $client = null;
    private static ObjectManager $entityManager;
    private static User $captain;
    private static Tournament $tournament;
    private static Team $team;
    private static User $unauthorizedUser;

    public static function setUpBeforeClass(): void
    {
        self::$client = static::createClient();
        self::$entityManager = static::getContainer()->get('doctrine')->getManager();
        self::$captain = self::$entityManager->getRepository(User::class)->findOneBy(['email' => 'captain5@test.com']);
        self::$tournament = self::$entityManager->getRepository(Tournament::class)->findOneBy(['name' => 'tournament5']);
        self::$team = self::$entityManager->getRepository(Team::class)->findOneBy(['name' => 'team5']);
        self::$unauthorizedUser = self::$entityManager->getRepository(User::class)->findOneBy(['email' => 'unauthorizedUser@test.com']);
    }

    public static function tearDownAfterClass(): void
    {
        self::ensureKernelShutdown();
    }

    protected function tearDown(): void
    {
        // Erase token value
        self::$client->setServerParameter('HTTP_Authorization', '');
    }

    public function testTeamJoinAndLeaveTournamentOk204()
    {
        // Auth user
         $this->authUser(self::$captain);

        // Request team_join_tournament
        self::$client->request('POST', '/api/tournaments/' . self::$tournament->getId() . '/teams/' . self::$team->getId() . '/join');

        // Assert response code
        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());

        // Request team_leave_tournament
        self::$client->request('delete', '/api/tournaments/' . self::$tournament->getId() . '/teams/' . self::$team->getId() . '/leave');

        // Assert response code and content-type
        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());
    }

    public function testTeamJoinAndLeaveTournamentFail403()
    {
        // 403 handled by src/security/appVoter
        // Auth user
        $this->authUser(self::$unauthorizedUser);

        // Request team_join_tournament
        self::$client->request('POST', '/api/tournaments/' . self::$tournament->getId() . '/teams/' . self::$team->getId() . '/join');

        // Assert response code 403 Forbidden
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());

        // Request team_leave_tournament
        self::$client->request('delete', '/api/tournaments/' . self::$tournament->getId() . '/teams/' . self::$team->getId() . '/leave');

        // Assert response code 403 Forbidden
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }


    public function testGetTournamentsOk200()
    {
        // Request get_tournaments
        self::$client->request('GET', '/api/tournaments');

        // Assert response code and content-type
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        // Get the response data as an associative array
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);

        // Assert that each tournament in the response has the follow keys
        // Serialization group "myTournament"
        foreach ($responseData as $data) {
            $this->assertArrayHasKey('id', $data);
            $this->assertArrayHasKey('name', $data);
            $this->assertArrayHasKey('points', $data);
            $this->assertArrayHasKey('max_teams', $data);
            $this->assertArrayHasKey('has_loser_bracket', $data);
            $this->assertArrayHasKey('type', $data);
            $this->assertArrayHasKey('cash_price', $data);
            $this->assertArrayHasKey('teams', $data);
            $this->assertArrayHasKey('created_at', $data);
            $this->assertArrayHasKey('start_at', $data);
            $this->assertArrayHasKey('is_started', $data);
            $this->assertArrayHasKey('is_finished', $data);
            $this->assertArrayHasKey('tournament_brackets', $data);
            $this->assertArrayHasKey('ranking', $data);
        }
    }

    public function testGetTournamentByIdOk200()
    {
        // Request get_tournament
        self::$client->request('GET', '/api/tournaments/' . self::$tournament->getId());

        // Assert response code
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        // Get the response data as an associative array
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);

        // Assert data matches the created Tournament, serialization group "myTournament"
        $this->assertEquals(self::$tournament->getId(), $responseData['id']);
        $this->assertEquals(self::$tournament->getName(), $responseData['name']);
        $this->assertEquals(self::$tournament->getPoints(), $responseData['points']);
        $this->assertEquals(self::$tournament->getMaxTeams(), $responseData['max_teams']);
        $this->assertEquals(self::$tournament->hasLoserBracket(), $responseData['has_loser_bracket']);
        $this->assertEquals(self::$tournament->getType(), $responseData['type']);
        $this->assertEquals(self::$tournament->getCashPrice(), $responseData['cash_price']);
        $this->assertEquals(self::$tournament->getTeams()->toArray(), $responseData['teams']);
        $this->assertEquals(self::$tournament->getCreatedAt()->format(DateTimeInterface::ATOM), $responseData['created_at']);
        $this->assertEquals(self::$tournament->getStartAt()->format(DateTimeInterface::ATOM), $responseData['start_at']);
        $this->assertEquals(self::$tournament->isStarted(), $responseData['is_started']);
        $this->assertEquals(self::$tournament->isFinished(), $responseData['is_finished']);
        $this->assertEquals(self::$tournament->getTournamentBrackets()[0]->getId(), $responseData['tournament_brackets'][0]['id']);
        $this->assertEquals(self::$tournament->getRanking()->getId(), $responseData['ranking']['id']);
    }

    protected function authUser(User $user): void
    {
        self::$client->request(
            'POST',
            '/api/open/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $user->getUserIdentifier(),
                'password' => 'password',
            ])
        );

        $data = json_decode(self::$client->getResponse()->getContent(), true);

        self::$client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));
    }

}