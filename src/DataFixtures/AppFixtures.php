<?php

namespace App\DataFixtures;

use App\Entity\User\PlayerUser;
use App\Entity\User\StaffUser;
use App\Model\User\Enum\UserEnum;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** Fixtures  used for every endToEnd testing */
class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $staffUser = new StaffUser();
        $staffUser
            ->setName('staff')
            ->setEmail("staff@test.com")
            ->setRoles([UserEnum::ROLE_STAFF])
            ->setPassword($this->hasher->hashPassword($staffUser, 'password'))
            ->setCreatedAt(new DateTime());

        $manager->persist($staffUser);

        $unauthorizedUser = new PlayerUser();
        $unauthorizedUser
            ->setEmail('unauthorizedUser' . "@test.com")
            ->setRoles([UserEnum::ROLE_PLAYER])
            ->setPassword($this->hasher->hashPassword($unauthorizedUser, 'password'))
            ->setCreatedAt(new \DateTime())
            ->setGamerTag('unauthorizedUser')
            ->setPoints(0);

        $manager->persist($unauthorizedUser);

        $manager->flush();
    }
}
