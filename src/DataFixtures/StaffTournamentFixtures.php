<?php

namespace App\DataFixtures;

use App\Entity\Team\Team;
use App\Entity\Tournament\Tournament;
use App\Entity\Tournament\TournamentBracket;
use App\Entity\Tournament\TournamentMatch;
use App\Entity\Tournament\TournamentRanking;
use App\Entity\Tournament\TournamentRound;
use App\Model\Bracket\Enum\BracketEnum;
use App\Tournament\State\InitialState;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** Fixtures used for StaffTournamentControllerTest */
class StaffTournamentFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $tournament1 = new Tournament(new InitialState());
        $ranking1 = new TournamentRanking();
        $bracket1 = new TournamentBracket();
        $round1 = new TournamentRound();
        $match1 = new TournamentMatch();
        $team1One = (new Team())->setName('teamOneMatch')->setCreatedAt(new DateTime())->setType('4s')->setPoints(0);
        $team1Two = (new Team())->setName('teamTwoMatch')->setCreatedAt(new DateTime())->setType('4ss')->setPoints(0);

        $tournament1
            ->setName('tournamentStaff')
            ->setType('4s')
            ->setPoints(70)
            ->setCreatedAt(new \DateTime('yesterday'))
            ->setMaxTeams(16)
            ->setHasLoserBracket(true)
            ->setCashPrice('100.50')
            ->setStartAt(new \DateTime('yesterday'))
            ->setIsFinished(false)
            ->setIsStarted(false)
            ->setRanking($ranking1);

        $ranking1->setPointsByTier([20, 15, 12, 10, 8, 5]);
        $ranking1->setTournament($tournament1);

        $bracket1
            ->setName(BracketEnum::WINNER_BRACKET->value)
            ->setTournament($tournament1);

        $round1
            ->setName('round 1')
            ->setBracket($bracket1)
            ->setBestOf(5)
            ->setIsFinish(false)
            ->setCreatedAt(new DateTime());

        $match1
            ->setName('A')
            ->setRound($round1)
            ->setTeamOne($team1One)
            ->setTeamTwo($team1Two);

        $tournament2 = new Tournament(new InitialState());
        $tournament2
            ->setName('tournamentDelete')
            ->setType('4s')
            ->setPoints(70)
            ->setCreatedAt(new \DateTime('yesterday'))
            ->setMaxTeams(16)
            ->setHasLoserBracket(true)
            ->setCashPrice('100.50')
            ->setStartAt(new \DateTime('yesterday'))
            ->setIsFinished(false)
            ->setIsStarted(false);

        $manager->persist($tournament1);
        $manager->persist($tournament2);
        $manager->persist($ranking1);
        $manager->persist($team1One);
        $manager->persist($team1Two);
        $manager->persist($bracket1);
        $manager->persist($round1);
        $manager->persist($match1);

        $manager->flush();
    }
}
