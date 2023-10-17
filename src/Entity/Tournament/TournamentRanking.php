<?php

namespace App\Entity\Tournament;

use App\Repository\Tournament\TournamentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TournamentRepository::class)]
class TournamentRanking
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\OneToOne(inversedBy: 'tournamentRanking', targetEntity: Tournament::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tournament $tournament = null;

    #[ORM\Column(nullable: false)]
    private array $pointsByTier = [];

    #[ORM\Column(nullable: true)]
    private array $result = [];

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(?Uuid $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function setTournament(?Tournament $tournament): self
    {
        $this->tournament = $tournament;
        return $this;
    }

    public function getPointsByTier(): array
    {
        return $this->pointsByTier;
    }

    public function setPointsByTier(array $pointsByTier): self
    {
        $this->pointsByTier = $pointsByTier;
        return $this;
    }

    public function getResult(): array
    {
        return $this->result;
    }

    public function setResult(array $result): self
    {
        $this->result = $result;
        return $this;
    }

}
