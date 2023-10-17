<?php

namespace App\Entity\User;

use App\Entity\Team\Team;
use App\Entity\Team\TeamInvite;
use App\Repository\User\PlayerUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerUserRepository::class)]
class PlayerUser extends User
{
    #[ORM\Column(length: 20, unique: true)]
    private ?string $gamerTag = null;

    #[ORM\Column]
    private ?int $points = null;

    #[ORM\Column]
    private ?bool $isCaptain = false;

    #[ORM\ManyToMany(targetEntity: Team::class, mappedBy: 'players')]
    private Collection $teams;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: TeamInvite::class, orphanRemoval: true)]
    private Collection $teamInvites;

    public function __construct()
    {
        $this->teams = new ArrayCollection();
        $this->teamInvites = new ArrayCollection();
    }

    public function getGamerTag(): ?string
    {
        return $this->gamerTag;
    }

    public function setGamerTag(string $gamerTag): self
    {
        $this->gamerTag = $gamerTag;

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

    public function IsCaptain(): ?bool
    {
        return $this->isCaptain;
    }

    public function setIsCaptain(?bool $isCaptain): self
    {
        $this->isCaptain = $isCaptain;
        return $this;
    }

    /**
     * @return Collection<int, Team>
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): self
    {
        if (!$this->teams->contains($team)) {
            $this->teams->add($team);
            $team->addPlayer($this);
        }

        return $this;
    }

    public function removeTeam(Team $team): self
    {
        if ($this->teams->removeElement($team)) {
            $team->removePlayer($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, TeamInvite>
     */
    public function getTeamInvites(): Collection
    {
        return $this->teamInvites;
    }

    public function addTeamInvite(TeamInvite $teamInvite): self
    {
        if (!$this->teamInvites->contains($teamInvite)) {
            $this->teamInvites->add($teamInvite);
            $teamInvite->setPlayer($this);
        }

        return $this;
    }

    public function removeTeamInvite(TeamInvite $teamInvite): self
    {
        if ($this->teamInvites->removeElement($teamInvite)) {
            // set the owning side to null (unless already changed)
            if ($teamInvite->getPlayer() === $this) {
                $teamInvite->setPlayer(null);
            }
        }

        return $this;
    }
}
