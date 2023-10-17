<?php

namespace App\Entity\Tournament;

use App\Entity\Team\Team;
use App\Repository\Tournament\TournamentMatchRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TournamentMatchRepository::class)]
#[ORM\InheritanceType("JOINED")]
#[ORM\DiscriminatorColumn(name: "discriminator", type: "string")]
#[ORM\DiscriminatorMap(['tournament_match' => TournamentMatch::class, 'tournament_match_final' => TournamentMatchFinal::class])]
class TournamentMatch
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 25)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'tournamentMatches')]
    #[ORM\JoinColumn(onDelete: "SET NULL")]
    private ?Team $teamOne = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: "SET NULL")]
    private ?Team $teamTwo = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $result = null;

    #[ORM\Column]
    private ?bool $isFinish = false;

    #[ORM\Column]
    private ?bool $isWaitingForAdmin = false;

    #[ORM\ManyToOne(cascade: ["persist"], fetch: 'EAGER', inversedBy: 'matches')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TournamentRound $round = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(?Uuid $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTeamOne(): ?Team
    {
        return $this->teamOne;
    }

    public function setTeamOne(?Team $teamOne): self
    {
        $this->teamOne = $teamOne;

        return $this;
    }

    public function getTeamTwo(): ?Team
    {
        return $this->teamTwo;
    }

    public function setTeamTwo(?Team $teamTwo): self
    {
        $this->teamTwo = $teamTwo;

        return $this;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(?string $result): self
    {
        $this->result = $result;

        return $this;
    }

    public function getRound(): ?TournamentRound
    {
        return $this->round;
    }

    public function setRound(?TournamentRound $round): self
    {
        $this->round = $round;

        return $this;
    }

    public function isFinish(): bool
    {
        return $this->isFinish;
    }

    public function setIsFinish(bool $isFinish): self
    {
        $this->isFinish = $isFinish;
        return $this;
    }

    public function isWaitingForAdmin(): bool
    {
        return $this->isWaitingForAdmin;
    }

    public function setIsWaitingForAdmin(bool $isWaitingForAdmin): self
    {
        $this->isWaitingForAdmin = $isWaitingForAdmin;
        return $this;
    }

    public function getLosingTeam(): ?Team
    {
        if ($this->result) {
            [$teamOneScore, $teamTwoScore] = explode('-', $this->result);
            if ($teamOneScore > $teamTwoScore) {
                return $this->teamTwo;
            } elseif ($teamOneScore < $teamTwoScore) {
                return $this->teamOne;
            }
        }
        return null;

    }

    public function getWinningTeam(): ?Team
    {
        if ($this->result) {
            [$teamOneScore, $teamTwoScore] = explode('-', $this->result);
            if ($teamOneScore < $teamTwoScore) {
                return $this->teamTwo;
            } elseif ($teamOneScore > $teamTwoScore) {
                return $this->teamOne;
            }
        }
        return null;

    }

}
