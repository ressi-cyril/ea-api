<?php

namespace App\Tests\EndToEnd\Staff;

use App\Entity\Tournament\Tournament;
use App\Entity\User\User;
use DateTime;
use DateTimeInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StaffTournamentControllerTest extends WebTestCase
{
    private static ?KernelBrowser $client = null;
    private static ObjectManager $entityManager;
    private static User $staffUser;
    private static User $unauthorizedUser;
    private static Tournament $tournament;

    public static function setUpBeforeClass(): void
    {
        // Initialize client and services
        self::$client = static::createClient();
        self::$entityManager = static::getContainer()->get('doctrine')->getManager();
        self::$staffUser = static::$entityManager->getRepository(User::class)->findOneBy(['email' => 'staff@test.com']);
        self::$unauthorizedUser = self::$entityManager->getRepository(User::class)->findOneBy(['email' => 'unauthorizedUser@test.com']);
        self::$tournament = self::$entityManager->getRepository(Tournament::class)->findOneBy(['name' => 'tournamentStaff']);
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

    public function testGetTournamentsOk200()
    {
        // Authenticate user
        $this->authUser(self::$staffUser);

        // Request staff_get_tournaments
        self::$client->request('GET', '/api/staff/tournaments');

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
        // Authenticate user
        $this->authUser(self::$staffUser);

        // Request staff_get_tournament
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

    public function testGetTournamentByIdFail403()
    {
        // 403, handle by security.yaml
        // Authenticate user
        $this->authUser(self::$unauthorizedUser);

        // Request staff_get_tournament
        self::$client->request('GET', '/api/staff/tournaments/' . self::$tournament->getId());

        // Assert response code
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    public function testPostTournamentOk200()
    {
        // Prepare data to submit
        $start_at = (new DateTime('tomorrow'))->format(DateTimeInterface::ATOM);
        $tournamentDto = [
            'name' => 'tournamentPostTest',
            'points' => 120,
            'max_teams' => 8,
            'has_loser_bracket' => true,
            'type' => '2s',
            'cash_price' => 50,
            'start_at' => $start_at,
            'points_by_tier' => [50, 30, 20, 10, 5, 5]
        ];

        //Authenticate user
        $this->authUser(self::$staffUser);

        // Request staff_post_tournament
        self::$client->request(
            'POST',
            '/api/staff/tournaments',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($tournamentDto)
        );

        // Assert response code
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        // Get the response data as an associative array
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);

        // Assert data matches the created Tournament, serialization group "myTournament"
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('tournamentPostTest', $responseData['name']);
        $this->assertEquals(120, $responseData['points']);
        $this->assertEquals(8, $responseData['max_teams']);
        $this->assertTrue($responseData['has_loser_bracket']);
        $this->assertEquals('2s', $responseData['type']);
        $this->assertEquals(50, $responseData['cash_price']);
        $this->assertArrayHasKey('teams', $responseData);
        $this->assertArrayHasKey('created_at', $responseData);
        $this->assertEquals($start_at, $responseData['start_at']);
        $this->assertFalse($responseData['is_started']);
        $this->assertFalse($responseData['is_finished']);
        $this->assertArrayHasKey('tournament_brackets', $responseData);
        $this->assertEquals([50, 30, 20, 10, 5, 5], $responseData['ranking']['points_by_tier']);
    }

    public function testPostTournamentFail403()
    {
        // 403, handle by security.yaml
        // Authenticate user
        $this->authUser(self::$unauthorizedUser);

        // Request staff_post_tournament
        self::$client->request(
            'POST',
            '/api/staff/tournaments',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        // Assert response code
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    public function testUpdateTournamentOk200()
    {
        // Prepare data to submit
        $start_at = (new DateTime('now'))->format(DateTimeInterface::ATOM);
        $tournamentDto = [
            'name' => 'tournamentUpdateTest',
            'points' => 140,
            'max_teams' => 4,
            'has_loser_bracket' => true,
            'type' => '4s',
            'cash_price' => 150,
            'start_at' => $start_at,
            'points_by_tier' => [50, 40, 30, 20]
        ];

        //Authenticate user
        $this->authUser(self::$staffUser);

        // Request staff_update_tournament
        self::$client->request(
            'PUT',
            '/api/staff/tournaments/' . self::$tournament->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($tournamentDto)
        );

        // Assert response code
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        // Get the response data as an associative array
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);

        // Assert data matches the created Tournament, serialization group "myTournament"
        $this->assertEquals(self::$tournament->getId(), $responseData['id']);
        $this->assertEquals('tournamentUpdateTest', $responseData['name']);
        $this->assertEquals(140, $responseData['points']);
        $this->assertEquals(4, $responseData['max_teams']);
        $this->assertTrue($responseData['has_loser_bracket']);
        $this->assertEquals('4s', $responseData['type']);
        $this->assertEquals(150, $responseData['cash_price']);
        $this->assertArrayHasKey('teams', $responseData);
        $this->assertArrayHasKey('created_at', $responseData);
        $this->assertEquals($start_at, $responseData['start_at']);
        $this->assertFalse($responseData['is_started']);
        $this->assertFalse($responseData['is_finished']);
        $this->assertArrayHasKey('tournament_brackets', $responseData);
        $this->assertEquals([50, 40, 30, 20], $responseData['ranking']['points_by_tier']);
    }

    public function testUpdateTournamentFail403()
    {
        // 403, handle by security.yaml
        // Authenticate user
        $this->authUser(self::$unauthorizedUser);

        // Request staff_update_tournament
        self::$client->request(
            'PUT',
            '/api/staff/tournaments/' . self::$tournament->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        // Assert response code
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    public function testDeleteTournamentOk204()
    {
        // Retrieve resources
        $tournamentToDelete = self::$entityManager->getRepository(Tournament::class)->findOneBy(['name' => 'tournamentDelete']);

        // Authenticate user
        $this->authUser(self::$staffUser);

        // Request staff_delete_tournament
        self::$client->request('delete', '/api/staff/tournaments/' . $tournamentToDelete->getId());

        // Assert response code
        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());
    }

    public function testDeleteTournamentFail403()
    {
        // 403, handle by security.yaml
        // Authenticate user
        $this->authUser(self::$unauthorizedUser);

        // Request staff_delete_tournament
        self::$client->request('delete', '/api/staff/tournaments/' . self::$tournament->getId());

        // Assert response code
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    public function testGetTournamentMatchesOk200()
    {
        // Authenticate user
        $this->authUser(self::$staffUser);

        // Request staff_get_matches
        self::$client->request('get', '/api/staff/tournaments/' . self::$tournament->getId() . '/matches');

        // Get the response data as an associative array
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);

        // Assert that each tournament in the response has the follow keys
        // Serialization group "myMatch"
        foreach ($responseData as $data) {
            $this->assertArrayHasKey('id', $data);
            $this->assertArrayHasKey('name', $data);
            $this->assertArrayHasKey('team_one', $data);
            $this->assertArrayHasKey('team_two', $data);
            $this->assertArrayHasKey('result', $data);
            $this->assertArrayHasKey('is_finish', $data);
            $this->assertArrayHasKey('is_waiting_for_admin', $data);
            $this->assertArrayHasKey('round', $data);
        }
    }

    public function testGetTournamentMatchesFail403()
    {
        // 403, handle by security.yaml
        // Authenticate user
        $this->authUser(self::$unauthorizedUser);

        // Request staff_get_matches
        self::$client->request('get', '/api/staff/tournaments/' . self::$tournament->getId() . '/matches');

        // Assert response code
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    private function authUser(User $user): void
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