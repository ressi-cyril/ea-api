<?php

namespace App\Entity\Tournament;

use App\Repository\Tournament\TournamentRoundRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TournamentRoundRepository::class)]
class TournamentRound
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 25)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $bestOf = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'tournamentRounds')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TournamentBracket $bracket = null;

    #[ORM\OneToMany(mappedBy: 'round', targetEntity: TournamentMatch::class, fetch: 'EAGER')]
    private Collection $matches;

    #[ORM\Column(nullable: true)]
    private array $infos = [];

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column]
    private bool $isFinish;

    public function __construct()
    {
        $this->matches = new ArrayCollection();
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

    public function getBestOf(): ?int
    {
        return $this->bestOf;
    }

    public function setBestOf(int $bestOf): self
    {
        $this->bestOf = $bestOf;

        return $this;
    }

    public function getBracket(): ?TournamentBracket
    {
        return $this->bracket;
    }

    public function setBracket(?TournamentBracket $bracket): self
    {
        $this->bracket = $bracket;

        return $this;
    }

    /**
     * @return Collection<int, TournamentMatch>
     */
    public function getMatches(): Collection
    {
        return $this->matches;
    }

    public function addMatch(TournamentMatch $match): self
    {
        if (!$this->matches->contains($match)) {
            $this->matches->add($match);
            $match->setRound($this);
        }

        return $this;
    }

    public function removeMatch(TournamentMatch $match): self
    {
        if ($this->matches->removeElement($match)) {
            // set the owning side to null (unless already changed)
            if ($match->getRound() === $this) {
                $match->setRound(null);
            }
        }

        return $this;
    }

    public function getInfos(): array
    {
        return $this->infos;
    }

    public function setInfos(array $infos): self
    {
        $this->infos = $infos;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function verifyMatchesAreOver(): bool
    {
        /** @var TournamentMatch $match */
        foreach ($this->getMatches() as $match) {
            if (!$match->isFinish() || $match->isWaitingForAdmin()) {
                return false;
            }
        }

        return true;
    }
}
