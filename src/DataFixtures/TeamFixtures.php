<?php

namespace App\DataFixtures;

use App\Entity\Team\Team;
use App\Entity\User\PlayerUser;
use App\Model\User\Enum\UserEnum;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** Fixtures used for TeamControllerTest */
class TeamFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $team1 = new Team();
        $team2 = new Team();
        $captain1 = new PlayerUser();
        $captain2 = new PlayerUser();
        $player1 = new PlayerUser();
        $player2 = new PlayerUser();

        $team1
            ->setName('team6')
            ->setPoints(0)
            ->setType('4s')
            ->setCreatedAt(new DateTime())
            ->addPlayer($captain1)
            ->setCaptain($captain1);

        $team2
            ->setName('team7-delete')
            ->setPoints(0)
            ->setType('4s')
            ->setCreatedAt(new DateTime())
            ->addPlayer($captain2)
            ->setCaptain($captain2);

        $captain1
            ->setEmail("captain6@test.com")
            ->setRoles([UserEnum::ROLE_CAPTAIN])
            ->setPassword($this->hasher->hashPassword($captain1, 'password'))
            ->setCreatedAt(new \DateTime())
            ->setGamerTag('captain6')
            ->setPoints(0)
            ->addTeam($team1);

        $captain2
            ->setEmail("captain7@test.com")
            ->setRoles([UserEnum::ROLE_CAPTAIN])
            ->setPassword($this->hasher->hashPassword($captain2, 'password'))
            ->setCreatedAt(new \DateTime())
            ->setGamerTag('captain7')
            ->setPoints(0)
            ->addTeam($team2);

        $player1
            ->setEmail("player6-1@test.com")
            ->setRoles([UserEnum::ROLE_PLAYER])
            ->setPassword($this->hasher->hashPassword($player1, 'password'))
            ->setCreatedAt(new \DateTime())
            ->setGamerTag('player61')
            ->setPoints(0);

        $player2
            ->setEmail("player7-1@test.com")
            ->setRoles([UserEnum::ROLE_PLAYER])
            ->setPassword($this->hasher->hashPassword($player2, 'password'))
            ->setCreatedAt(new \DateTime())
            ->setGamerTag('player71')
            ->setPoints(0);

        $manager->persist($team1);
        $manager->persist($team2);
        $manager->persist($captain1);
        $manager->persist($captain2);
        $manager->persist($player1);
        $manager->persist($player2);

        $manager->flush();
    }
}
