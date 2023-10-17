<?php

namespace App\Entity\Team;

use App\Entity\Tournament\Tournament;
use App\Entity\Tournament\TournamentMatch;
use App\Entity\User\PlayerUser;
use App\Repository\Team\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
#[ORM\InheritanceType("JOINED")]
#[ORM\DiscriminatorColumn(name: "discriminator", type: "string")]
#[ORM\DiscriminatorMap(['team' => Team::class, 'team_fake' => TeamFake::class])]
class Team
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 25)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $points = null;

    #[ORM\Column(length: 25)]
    private ?string $type = null;

    #[ORM\ManyToMany(targetEntity: PlayerUser::class, inversedBy: 'teams')]
    private Collection $players;

    #[ORM\ManyToOne(cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?PlayerUser $captain = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToMany(targetEntity: Tournament::class, mappedBy: 'teams')]
    private Collection $tournaments;

    #[ORM\OneToMany(mappedBy: 'teamOne', targetEntity: TournamentMatch::class)]
    private Collection $tournamentMatches;

    #[ORM\OneToMany(mappedBy: 'team', targetEntity: TeamInvite::class, orphanRemoval: true)]
    private Collection $invites;

    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->tournaments = new ArrayCollection();
        $this->tournamentMatches = new ArrayCollection();
        $this->invites = new ArrayCollection();
    }

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

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;

        return $this;
    }

    public function addPoints(int $points): self
    {
        $this->points += $points;

        return $this;
    }

    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(PlayerUser $player): self
    {
        if (!$this->players->contains($player)) {
            $this->players->add($player);
        }

        return $this;
    }

    public function removePlayer(PlayerUser $player): self
    {
        $this->players->removeElement($player);

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCaptain(): ?PlayerUser
    {
        return $this->captain;
    }

    public function setCaptain(?PlayerUser $captain): self
    {
        $this->captain = $captain;

        return $this;
    }

    /**
     * @return Collection<int, Tournament>
     */
    public function getTournaments(): Collection
    {
        return $this->tournaments;
    }

    public function addTournament(Tournament $tournament): self
    {
        if (!$this->tournaments->contains($tournament)) {
            $this->tournaments->add($tournament);
            $tournament->addTeam($this);
        }

        return $this;
    }

    public function removeTournament(Tournament $tournament): self
    {
        if ($this->tournaments->removeElement($tournament)) {
            $tournament->removeTeam($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, TournamentMatch>
     */
    public function getTournamentMatches(): Collection
    {
        return $this->tournamentMatches;
    }

    public function addTournamentMatch(TournamentMatch $tournamentMatch): self
    {
        if (!$this->tournamentMatches->contains($tournamentMatch)) {
            $this->tournamentMatches->add($tournamentMatch);
            $tournamentMatch->setTeamOne($this);
        }

        return $this;
    }

    public function removeTournamentMatch(TournamentMatch $tournamentMatch): self
    {
        if ($this->tournamentMatches->removeElement($tournamentMatch)) {
            // set the owning side to null (unless already changed)
            if ($tournamentMatch->getTeamOne() === $this) {
                $tournamentMatch->setTeamOne(null);
            }
        }

        return $this;
    }

    public function isTeamPlayer(PlayerUser $player): bool
    {
        return in_array($player, $this->getPlayers()->toArray(), true);
    }

    /**
     * @return Collection<int, TeamInvite>
     */
    public function getInvites(): Collection
    {
        return $this->invites;
    }

    public function addInvite(TeamInvite $invite): self
    {
        if (!$this->invites->contains($invite)) {
            $this->invites->add($invite);
            $invite->setTeam($this);
        }

        return $this;
    }

    public function removeInvite(TeamInvite $invite): self
    {
        if ($this->invites->removeElement($invite)) {
            // set the owning side to null (unless already changed)
            if ($invite->getTeam() === $this) {
                $invite->setTeam(null);
            }
        }

        return $this;
    }
}
