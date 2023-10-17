<?php

namespace App\DataFixtures;

use App\Entity\Team\Team;
use App\Entity\Tournament\Tournament;
use App\Entity\Tournament\TournamentRanking;
use App\Entity\User\PlayerUser;
use App\Tournament\State\InitialState;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** Fixtures used for StaffTournamentProgressionControllerTest */
class StaffTournamentProgressionFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $tournament = new Tournament(new InitialState());
        $ranking = new TournamentRanking();

        $tournament
            ->setName('tournamentProgression')
            ->setType('4s')
            ->setPoints(70)
            ->setCreatedAt(new \DateTime('yesterday'))
            ->setMaxTeams(16)
            ->setHasLoserBracket(true)
            ->setCashPrice('100.50')
            ->setStartAt(new \DateTime('yesterday'))
            ->setIsFinished(false)
            ->setIsStarted(false)
            ->setRanking($ranking);

        $ranking->setPointsByTier([20, 15, 12, 10, 8, 5]);
        $ranking->setTournament($tournament);

        for ($i = 0; $i <= 15; $i++) {
            $player = new PlayerUser();
            $player
                ->setCreatedAt(new \DateTime())
                ->setEmail('player' . $i . '@mail.com')
                ->setRoles(['ROLE_CAPTAIN'])
                ->setPassword($this->hasher->hashPassword($player, 'password'))
                ->setGamerTag('Player' . $i)
                ->setPoints(0);

            $team = new Team();
            $team
                ->setName('team1' . $i)
                ->setCaptain($player)
                ->addPlayer($player)
                ->setPoints($i)
                ->setType('4s')
                ->setCreatedAt(new \DateTime());

            $team->addTournament($tournament);

            $manager->persist($player);
            $manager->persist($team);
        }
        $manager->persist($ranking);
        $manager->persist($tournament);

        $manager->flush();
    }
}
