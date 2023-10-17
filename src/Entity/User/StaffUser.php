<?php

namespace App\Entity\User;

use App\Repository\User\PlayerUserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerUserRepository::class)]
class StaffUser extends User
{
    #[ORM\Column(length: 20)]
    private ?string $name = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
