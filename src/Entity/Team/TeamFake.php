<?php

namespace App\Entity\Team;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TeamFake extends Team
{
    public const FAKE_NAME = 'teamFake';
}
