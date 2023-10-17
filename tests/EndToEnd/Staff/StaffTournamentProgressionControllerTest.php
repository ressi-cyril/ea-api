<?php

namespace App\Tests\EndToEnd\Staff;

use App\Entity\Tournament\Tournament;
use App\Entity\Tournament\TournamentBracket;
use App\Entity\User\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StaffTournamentProgressionControllerTest extends WebTestCase
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
        self::$staffUser = self::$entityManager->getRepository(User::class)->findOneBy(['email' => 'staff@test.com']);
        self::$unauthorizedUser = self::$entityManager->getRepository(User::class)->findOneBy(['email' => 'unauthorizedUser@test.com']);
        self::$tournament = self::$entityManager->getRepository(Tournament::class)->findOneBy(['name' => 'tournamentProgression']);
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

    public function testStartTournamentOk204()
    {
        // Prepare data to submit
        $roundDto = [
            'best_of' => 5,
            'infos' => ['infos']
        ];

        // Authenticate user
        $this->authUser(self::$staffUser);

        // Request staff_tournament_start
        self::$client->request(
            'POST',
            '/api/staff/tournaments/' . self::$tournament->getId() . '/start',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($roundDto)
        );

        // Assert response code
        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());
    }

    public function testStartTournamentFail403()
    {
        // 403, handle by security.yaml
        // Authenticate user
        $this->authUser(self::$unauthorizedUser);

        // Request start_tournament
        self::$client->request(
            'POST',
            '/api/staff/tournaments/' . self::$tournament->getId() . '/start',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        // Assert response code
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    public function testEndRoundOk204()
    {
        // Update resources
        self::$tournament = $this->finishTournamentMatch();

        // Authenticate user
        $this->authUser(self::$staffUser);

        // Request end_round
        self::$client->request(
            'PATCH',
            '/api/staff/tournaments/' . self::$tournament->getId() . '/rounds/' . self::$tournament->getTournamentBrackets()->first()->getTournamentRounds()->first()->getId(
            ) . '/end'
        );

        // Assert response code
        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());
    }

    public function testEndRoundFail403()
    {
        // 403, handle by security.yaml
        // Authenticate user
        $this->authUser(self::$unauthorizedUser);

        // Request end_round
        self::$client->request(
            'PATCH',
            '/api/staff/tournaments/' . self::$tournament->getId() . '/rounds/' . self::$tournament->getTournamentBrackets()->first()->getTournamentRounds()->first()->getId(
            ) . '/end'
        );

        // Assert response code
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    public function testContinueTournamentOk204()
    {
        // Prepare data to submit
        $roundDto = [
            'best_of' => 5,
            'infos' => ['infos']
        ];

        // Authenticate user
        $this->authUser(self::$staffUser);

        // Request continue_tournament
        self::$client->request(
            'POST',
            '/api/staff/tournaments/' . self::$tournament->getId() . '/continue',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($roundDto)
        );

        // Assert response code
        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());
    }

    public function testContinueTournamentFail403()
    {
        // 403, handle by security.yaml
        // Authenticate user
        $this->authUser(self::$unauthorizedUser);

        // Request continue_tournament
        self::$client->request(
            'POST',
            '/api/staff/tournaments/' . self::$tournament->getId() . '/continue',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        // Assert response code
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    public function testUpdateTournamentMatchResultOk204()
    {
        // Prepare data to submit
        $MatchDto = ['score' => '0-3'];
        self::$entityManager->refresh(self::$tournament);

        // Authenticate user
        $this->authUser(self::$staffUser);

        // Request staff_update_matches
        self::$client->request(
            'PATCH',
            '/api/staff/tournaments/' . self::$tournament->getId() . '/matches/' . self::$tournament->getTournamentBrackets()->first()->getTournamentRounds()->last()->getMatches(
            )->last()->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($MatchDto)
        );

        // Assert response code
        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());
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

    private function finishTournamentMatch(): Tournament
    {
        $tournament = self::$entityManager->getRepository(Tournament::class)->findOneBy(['name' => 'tournamentProgression']);
        $brackets = $tournament->getTournamentBrackets();

        /** @var TournamentBracket $bracket */
        foreach ($brackets as $bracket) {
            foreach ($bracket->getTournamentRounds() as $round) {
                foreach ($round->getMatches() as $match) {
                    $match
                        ->setIsFinish(true)
                        ->setResult('3-0');
                }
            }
        }
        self::$entityManager->flush();
        return $tournament;
    }

}