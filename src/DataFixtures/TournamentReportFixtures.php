<?php

namespace App\DataFixtures;

use App\Entity\Team\Team;
use App\Entity\Tournament\Tournament;
use App\Entity\Tournament\TournamentBracket;
use App\Entity\Tournament\TournamentMatch;
use App\Entity\Tournament\TournamentRound;
use App\Entity\User\PlayerUser;
use App\Model\User\Enum\UserEnum;
use App\Tournament\State\InProgressState;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** Fixtures used for TournamentReportControllerTest */
class TournamentReportFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $tournament = new Tournament(new InProgressState());
        $match = new TournamentMatch();
        $round = new TournamentRound();
        $bracket = new TournamentBracket();
        $teamOne = new Team();
        $teamTwo = new Team();
        $captainOne = new PlayerUser();
        $captainTwo = new PlayerUser();

        $teamOne
            ->setName('team4One')
            ->setPoints(0)
            ->setType('4s')
            ->setCreatedAt(new DateTime())
            ->addPlayer($captainOne)
            ->setCaptain($captainOne);

        $teamTwo
            ->setName('team4Two')
            ->setPoints(0)
            ->setType('4s')
            ->setCreatedAt(new DateTime())
            ->addPlayer($captainTwo)
            ->setCaptain($captainTwo);

        $captainOne
            ->setEmail("captain4One@test.com")
            ->setRoles([UserEnum::ROLE_CAPTAIN])
            ->setPassword($this->hasher->hashPassword($captainOne, 'password'))
            ->setCreatedAt(new \DateTime())
            ->setGamerTag('captain4One')
            ->setPoints(0)
            ->addTeam($teamOne);

        $captainTwo
            ->setEmail("captain4Two@test.com")
            ->setRoles([UserEnum::ROLE_CAPTAIN])
            ->setPassword($this->hasher->hashPassword($captainTwo, 'password'))
            ->setCreatedAt(new \DateTime())
            ->setGamerTag('captain4Two')
            ->setPoints(0)
            ->addTeam($teamTwo);

        $tournament
            ->setName('tournamentReport')
            ->setPoints(100)
            ->setMaxTeams(16)
            ->setHasLoserBracket(true)
            ->setType('2s')
            ->setCashPrice('10.20')
            ->setCreatedAt(new DateTime())
            ->setStartAt(new DateTime('tomorrow'))
            ->setIsStarted(false)
            ->setIsFinished(false)
            ->addTeam($teamOne)
            ->addTeam($teamTwo);

        $bracket
            ->setName('wb')
            ->setTournament($tournament);

        $round
            ->setName('wr1')
            ->setBestOf(3)
            ->setBracket($bracket)
            ->setIsFinish(false)
            ->setCreatedAt(new DateTime());

        $tournament
            ->addTeam($teamOne)
            ->addTeam($teamTwo);

        $match
            ->setTeamOne($teamOne)
            ->setTeamTwo($teamTwo)
            ->setRound($round)
            ->setName('B');

        $manager->persist($tournament);
        $manager->persist($match);
        $manager->persist($round);
        $manager->persist($bracket);
        $manager->persist($teamOne);
        $manager->persist($teamTwo);
        $manager->persist($captainOne);
        $manager->persist($captainTwo);

        $manager->flush();
    }
}
