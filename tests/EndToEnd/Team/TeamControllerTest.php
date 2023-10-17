<?php

namespace App\Tests\EndToEnd\Team;

use App\Entity\Team\Team;
use App\Entity\User\User;
use DateTimeInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TeamControllerTest extends WebTestCase
{
    private static ?KernelBrowser $client = null;
    private static ObjectManager $entityManager;
    private static User $captain;
    private static Team $team;
    private static User $player;
    private static User $playerInvited;
    private static User $unauthorizedUser;

    public static function setUpBeforeClass(): void
    {
        self::$client = static::createClient();
        self::$entityManager = static::getContainer()->get('doctrine')->getManager();
        self::$captain = self::$entityManager->getRepository(User::class)->findOneBy(['email' => 'captain6@test.com']);
        self::$team = self::$entityManager->getRepository(Team::class)->findOneBy(['name' => 'team6']);
        self::$player = self::$entityManager->getRepository(User::class)->findOneBy(['email' => 'player6-1@test.com']);
        self::$playerInvited = self::$entityManager->getRepository(User::class)->findOneBy(['email' => 'player7-1@test.com']);
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

    public function testGetTeamsOk200()
    {
        //Request get_tournaments
        self::$client->request('GET', '/api/teams');

        // Assert response code and content-type
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        // Get the response data as an associative array
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);

        // Assert that each tournament in the response has the follow keys
        // Serialization group "myTeam"
        foreach ($responseData as $data) {
            $this->assertArrayHasKey('id', $data);
            $this->assertArrayHasKey('name', $data);
            $this->assertArrayHasKey('points', $data);
            $this->assertArrayHasKey('type', $data);
            $this->assertArrayHasKey('players', $data);
            $this->assertArrayHasKey('captain', $data);
            $this->assertArrayHasKey('created_at', $data);
        }
    }

    public function testGetTeamByIdOk200()
    {
        //Request get_tournament
        self::$client->request('GET', '/api/teams/' . self::$team->getId());

        // Assert response code and content-type
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        // Get the response data as an associative array
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);

        // Assert data matches the created Team
        $this->assertEquals($responseData['id'], self::$team->getId());
        $this->assertEquals($responseData['name'], self::$team->getName());
        $this->assertEquals($responseData['points'], self::$team->getPoints());
        $this->assertEquals($responseData['type'], self::$team->getType());
        $this->assertEquals($responseData['players'][0]['id'], self::$team->getPlayers()[0]->getId());
        $this->assertEquals($responseData['captain']['id'], self::$team->getCaptain()->getId());
        $this->assertEquals($responseData['created_at'], self::$team->getCreatedAt()->format(DateTimeInterface::ATOM));
    }

    public function testPostTeamOk200()
    {
        // Prepare data to submit, "teamCreate" validation
        $teamDto = [
            'name' => 'TeamPost200',
            'player_captain' => self::$player->getId(),
            'type' => '2s'
        ];

        // Authenticate the user
        $this->authUser(self::$player);

        // Request post_team
        self::$client->request(
            'POST',
            '/api/teams',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($teamDto)
        );

        // Assert response code
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        // Get the response data as an associative array
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);

        // Assert data matches the created Team, serialization group "myTeam"
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('TeamPost200', $responseData['name']);
        $this->assertEquals(0, $responseData['points']);
        $this->assertEquals('2s', $responseData['type']);
        $this->assertEquals(self::$player->getId(), $responseData['players'][0]['id']);
        $this->assertEquals(self::$player->getId(), $responseData['captain']['id']);
        $this->assertArrayHasKey('created_at', $responseData);
    }

    public function testPostTeamFail401()
    {
        // 401, handle by #[IsGranted('IS_AUTHENTICATED')], this test is not really needed since it's  part of the Symfony package.
        // Request post_team
        self::$client->request(
            'POST',
            '/api/teams',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        $this->assertEquals(401, self::$client->getResponse()->getStatusCode());
    }

    public function testUpdateTeamOk200()
    {
        // Prepare data to submit
        $teamDto = [
            'name' => 'team6',
            'player_captain' => self::$captain->getId(),
        ];

        // Authenticate the user
        $this->authUser(self::$captain);

        // Request update_team
        self::$client->request(
            'PUT',
            '/api/teams/' . self::$team->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($teamDto)
        );

        // Assert response code
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        // Get the response data as an associative array
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);

        // Assert data matches the updated Team, serialization group "myTeam"
        $this->assertEquals(self::$team->getId(), $responseData['id']);
        $this->assertEquals('team6', $responseData['name']);
        $this->assertEquals(0, $responseData['points']);
        $this->assertEquals('4s', $responseData['type']);
        $this->assertEquals(self::$team->getCaptain()->getId(), $responseData['players'][0]['id']);
        $this->assertEquals(self::$team->getCaptain()->getId(), $responseData['captain']['id']);
        $this->assertEquals(self::$team->getCreatedAt()->format(DateTimeInterface::ATOM), $responseData['created_at']);
    }

    public function testUpdateTeamFail403()
    {
        // 403 handled by src/security/appVoter
        // Authenticate the user
        $this->authUser(self::$unauthorizedUser);

        // Request update_team
        self::$client->request(
            'PUT',
            '/api/teams/' . self::$team->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        // Assert response code
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    public function testDeleteTeamOk204()
    {
        // Retrieve resources
        $captain = self::$entityManager->getRepository(User::class)->findOneBy(['email' => 'captain7@test.com']);
        self::$entityManager->refresh($captain);

        // Authenticate user
        $this->authUser($captain);

        // Request update_team
        self::$client->request('delete', '/api/teams/' . $captain->getTeams()[0]->getId());

        // Assert response code
        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());
    }

    public function testDeleteTeamFail403()
    {
        // 403 handled by src/security/appVoter
        // Authenticate  user
        $this->authUser(self::$unauthorizedUser);

        // Request update_team
        self::$client->request('delete', '/api/teams/' . self::$team->getId());

        // Assert response code
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    public function testInvitePlayerOk200()
    {
        // Authenticate user
        $this->authUser(self::$captain);

        // Request invite_player_team
        self::$client->request('post', '/api/teams/' . self::$team->getId() . '/players/' . self::$playerInvited->getId() . '/invite');

        // Assert response code
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        // Get the response data as an associative array
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);

        // Assert data matches the TeamInvite, serialization group "myInvite"
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(self::$team->getId(), $responseData['team']['id']);
        $this->assertEquals(self::$playerInvited->getId(), $responseData['player']['id']);
        $this->assertArrayHasKey('created_at', $responseData);
    }

    public function testInvitePlayerFail403()
    {
        // 403 handled by src/security/appVoter
        // Authenticate  user
        $this->authUser(self::$unauthorizedUser);

        // Request invite_player_team
        self::$client->request('post', '/api/teams/' . self::$team->getId() . '/players/' . self::$playerInvited->getId() . '/invite');

        // Assert response code
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    public function testPlayerDeclineTeamOk204()
    {
        // Authenticate user
        $this->authUser(self::$playerInvited);

        // Request player_decline_team
        self::$client->request('delete', '/api/teams/' . self::$team->getId() . '/players/' . self::$playerInvited->getId() . '/decline');

        // Assert response code
        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());
    }

    public function testPlayerDeclineTeamFail403()
    {
        // 403 handled by src/security/appVoter
        // Authenticate user
        $this->authUser(self::$unauthorizedUser);

        // Request player_decline_team
        self::$client->request('delete', '/api/teams/' . self::$team->getId() . '/players/' . self::$playerInvited->getId() . '/decline');

        // Assert response code
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    public function testPlayerJoinTeamOk200()
    {
        // Authenticate user
        $this->authUser(self::$captain);

        // Invite player to join team first
        // Request invite_player_team
        self::$client->request('post', '/api/teams/' . self::$team->getId() . '/players/' . self::$playerInvited->getId() . '/invite');

        // Authenticate user
        $this->authUser(self::$playerInvited);

        // Request player_join_team
        self::$client->request('post', '/api/teams/' . self::$team->getId() . '/players/' . self::$playerInvited->getId() . '/join');

        // Assert response code
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        // Get the response data as an associative array
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);

        // Assert data matches the Team
        $this->assertEquals($responseData['id'], self::$team->getId());
        $this->assertEquals($responseData['name'], self::$team->getName());
        $this->assertEquals($responseData['points'], self::$team->getPoints());
        $this->assertEquals($responseData['type'], self::$team->getType());
        $this->assertEquals($responseData['players'][0]['id'], self::$team->getPlayers()[0]->getId());
        $this->assertEquals($responseData['captain']['id'], self::$team->getCaptain()->getId());
        $this->assertEquals($responseData['created_at'], self::$team->getCreatedAt()->format(DateTimeInterface::ATOM));
    }

    public function testPlayerJoinTeamFail403()
    {
        // 403 handled by src/security/appVoter
        // Authenticate user
        $this->authUser(self::$unauthorizedUser);

        // Request player_join_team
        self::$client->request('post', '/api/teams/' . self::$team->getId() . '/players/' . self::$playerInvited->getId() . '/join');

        // Assert response code
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    public function testPlayerLeaveTeamOk204()
    {
        // Authenticate user
        $this->authUser(self::$playerInvited);

        // Request player_leave_team
        self::$client->request('delete', '/api/teams/' . self::$team->getId() . '/players/' . self::$playerInvited->getId() . '/leave');

        // Assert response code
        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());
    }

    public function testPlayerLeaveTeamFail403()
    {
        // 403 handled by src/security/appVoter
        // Authenticate user
        $this->authUser(self::$unauthorizedUser);

        // Request player_leave_team
        self::$client->request('delete', '/api/teams/' . self::$team->getId() . '/players/' . self::$playerInvited->getId() . '/leave');

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