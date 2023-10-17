<?php

namespace App\DataFixtures;

use App\Entity\Team\Team;
use App\Entity\Tournament\Tournament;
use App\Entity\Tournament\TournamentBracket;
use App\Entity\Tournament\TournamentRanking;
use App\Entity\User\PlayerUser;
use App\Model\User\Enum\UserEnum;
use App\Tournament\State\InitialState;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** Fixtures used for TournamentControllerTest */
class TournamentFixtures extends Fixture
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
        $bracket = new TournamentBracket();
        $team = new Team();
        $captain = new PlayerUser();

        $tournament
            ->setName('tournament5')
            ->setType('4s')
            ->setPoints(70)
            ->setCreatedAt(new \DateTime('yesterday'))
            ->setMaxTeams(16)
            ->setHasLoserBracket(true)
            ->setCashPrice('100.50')
            ->setStartAt(new \DateTime('yesterday'))
            ->setIsFinished(false)
            ->setIsStarted(false)
            ->setRanking($ranking)
            ->addTournamentBracket($bracket);

        $ranking
            ->setPointsByTier([20, 15, 12, 10, 8, 5])
            ->setTournament($tournament);

        $bracket
            ->setName('winner bracket')
            ->setTournament($tournament);

        $team
            ->setName('team5')
            ->setPoints(0)
            ->setType('4s')
            ->setCreatedAt(new DateTime())
            ->addPlayer($captain)
            ->setCaptain($captain);

        $captain
            ->setEmail("captain5@test.com")
            ->setRoles([UserEnum::ROLE_CAPTAIN])
            ->setPassword($this->hasher->hashPassword($captain, 'password'))
            ->setCreatedAt(new \DateTime())
            ->setGamerTag('captain5')
            ->setPoints(0)
            ->addTeam($team);

        $manager->persist($tournament);
        $manager->persist($ranking);
        $manager->persist($team);
        $manager->persist($captain);
        $manager->persist($bracket);

        $manager->flush();
    }
}
