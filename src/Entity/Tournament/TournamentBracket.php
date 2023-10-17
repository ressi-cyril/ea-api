<?php

namespace App\Entity\Tournament;

use App\Repository\Tournament\TournamentBracketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TournamentBracketRepository::class)]
class TournamentBracket
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 25)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'tournamentBrackets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tournament $tournament = null;

    #[ORM\OneToMany(mappedBy: 'bracket', targetEntity: TournamentRound::class, cascade: ['persist'], fetch: "EAGER", orphanRemoval: true)]
    #[ORM\OrderBy(["createdAt" => "ASC"])]
    private Collection $tournamentRounds;

    public function __construct()
    {
        $this->tournamentRounds = new ArrayCollection();
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

    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function setTournament(?Tournament $tournament): self
    {
        $this->tournament = $tournament;

        return $this;
    }

    /**
     * @return Collection<int, TournamentRound>
     */
    public function getTournamentRounds(): Collection
    {
        return $this->tournamentRounds;
    }

    public function addTournamentRound(TournamentRound $tournamentRound): self
    {
        if (!$this->tournamentRounds->contains($tournamentRound)) {
            $this->tournamentRounds->add($tournamentRound);
            $tournamentRound->setBracket($this);
        }

        return $this;
    }

    public function removeTournamentRound(TournamentRound $tournamentRound): self
    {
        if ($this->tournamentRounds->removeElement($tournamentRound)) {
            // set the owning side to null (unless already changed)
            if ($tournamentRound->getBracket() === $this) {
                $tournamentRound->setBracket(null);
            }
        }

        return $this;
    }
}
