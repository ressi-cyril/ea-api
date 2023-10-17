<?php

namespace App\Tests\EndToEnd\Tournament;

use App\Entity\Tournament\Tournament;
use App\Entity\Tournament\TournamentMatch;
use App\Entity\User\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TournamentReportControllerTest extends WebTestCase
{
    private static ?KernelBrowser $client = null;
    private static ObjectManager $entityManager;
    private static User $unauthorizedUser;
    private static Tournament $tournament;
    private static User $captainTeamOne;
    private static TournamentMatch $match;

    public static function setUpBeforeClass(): void
    {
        // Initialize client and services
        self::$client = static::createClient();
        self::$entityManager = static::getContainer()->get('doctrine')->getManager();
        self::$unauthorizedUser = self::$entityManager->getRepository(User::class)->findOneBy(['email' => 'unauthorizedUser@test.com']);
        self::$captainTeamOne = self::$entityManager->getRepository(User::class)->findOneBy(['email' => 'captain4One@test.com']);
        self::$match = self::$entityManager->getRepository(TournamentMatch::class)->findOneBy(['name' => 'B']);
        self::$tournament = self::$entityManager->getRepository(Tournament::class)->findOneBy(['name' => 'tournamentReport']);
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

    public function testReportScoreOk204()
    {
        // Prepare data to submit
        $matchDto = ['score' => '3-0'];

        // Authenticate the user
        $this->authUser(self::$captainTeamOne);

        // Request report_score
        self::$client->request(
            'PATCH',
            '/api/tournaments/' . self::$tournament->getId() . '/matches/' . self::$match->getId() . '/report-score',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($matchDto)
        );

        // Assert response code
        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());
    }

    public function testReportScoreFail403()
    {
        // 403, handled by src/security/appVoter
        // Authenticate user
        $this->authUser(self::$unauthorizedUser);

        // Request report_score
        self::$client->request(
            'PATCH',
            '/api/tournaments/' . self::$tournament->getId() . '/matches/' . self::$match->getId() . '/report-score',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        // Assert response code 403 Forbidden
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    public function testReportAdminOk204()
    {
        // Authenticate the user
        $this->authUser(self::$captainTeamOne);

        // Request report_admin
        self::$client->request('PATCH', '/api/tournaments/' . self::$tournament->getId() . '/matches/' . self::$match->getId() . '/report-admin');

        // Assert response code
        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());
    }

    public function testReportAdminFail403()
    {
        // 403, handled by src/security/appVoter
        // Authenticate user
        $this->authUser(self::$unauthorizedUser);

        // Request report_admin
        self::$client->request('PATCH', '/api/tournaments/' . self::$tournament->getId() . '/matches/' . self::$match->getId() . '/report-admin');

        // Assert response code 403 Forbidden
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