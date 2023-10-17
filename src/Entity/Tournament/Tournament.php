<?php

namespace App\Entity\Tournament;

use App\Entity\Team\Team;
use App\Model\Round\Dto\RoundDto;
use App\Repository\Tournament\TournamentRepository;
use App\Tournament\Dto\TournamentDto;
use App\Tournament\Enum\TournamentStatesEnum;
use App\Tournament\Interface\TournamentStateInterface;
use App\Tournament\State\FinishedState;
use App\Tournament\State\InitialState;
use App\Tournament\State\InProgressState;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TournamentRepository::class)]
class Tournament
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

    #[ORM\Column]
    private int $maxTeams;

    #[ORM\Column]
    private bool $hasLoserBracket;

    #[ORM\Column(length: 25)]
    private ?string $type = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 7, scale: 2)]
    private ?string $cashPrice = null;

    #[ORM\ManyToMany(targetEntity: Team::class, inversedBy: 'tournaments')]
    private Collection $teams;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startAt = null;

    #[ORM\Column]
    private ?bool $isStarted = null;

    #[ORM\Column]
    private ?bool $isFinished = null;

    #[ORM\OneToMany(mappedBy: 'tournament', targetEntity: TournamentBracket::class, cascade: ['persist'], fetch: 'EAGER', orphanRemoval: true)]
    private Collection $tournamentBrackets;

    #[ORM\OneToOne(mappedBy: 'tournament', targetEntity: TournamentRanking::class, cascade: ['remove'])]
    private ?TournamentRanking $ranking = null;

    #[ORM\Column(name: 'state', type: 'string', enumType: TournamentStatesEnum::class)]
    private ?TournamentStatesEnum $stateString = null;

    private TournamentStateInterface $state;

    public function __construct(TournamentStateInterface $state)
    {
        $this->teams = new ArrayCollection();
        $this->tournamentBrackets = new ArrayCollection();

        $this->transitionTo($state);
    }

    /**
     * Transition to another State
     *
     * @param TournamentStateInterface $state
     * @return void
     */
    public function transitionTo(TournamentStateInterface $state): void
    {
        $this->state = $state;
        $this->stateString = $state->getStateEnum();
        $this->state->setTournament($this);
    }

    /**
     * Initialize the tournament by delegating to the current state.
     *
     * @param TournamentDto $tournamentDto
     * @return $this
     */
    public function initializeTournament(TournamentDto $tournamentDto): self
    {
        $this->state->initializeTournament($tournamentDto);
        return $this;
    }

    /**
     * Updating the tournament by delegating to the current state.
     *
     * @param TournamentDto $tournamentDto
     * @return $this
     */
    public function updateTournament(TournamentDto $tournamentDto): self
    {
        $this->state->updateTournament($tournamentDto);
        return $this;
    }

    /**
     * Deleting the tournament by delegating to the current state.
     *
     * @return void
     */
    public function deleteTournament(): void
    {
        $this->state->deleteTournament();
    }

    /**
     * Team join or leave tournament by delegating to the current state.
     *
     * @param Team|null $team
     * @param bool|null $isJoining
     * @return void
     */
    public function teamJoinOrLeaveTournament(?Team $team = null, ?bool $isJoining = null): void
    {
        $this->state->teamJoinOrLeaveTournament($team, $isJoining);
    }

    /**
     * Start tournament by delegating to the current state.
     *
     * @param RoundDto $roundDto
     * @return $this
     */
    public function startTournament(RoundDto $roundDto): self
    {
        $this->state->startTournament($roundDto);

        return $this;
    }

    /**
     * Continue tournament progression by delegating to the current state.
     *
     * @param RoundDto $roundDto
     * @return $this
     */
    public function continueTournament(RoundDto $roundDto): self
    {
        $this->state->continueTournament($roundDto);

        return $this;
    }

    /**
     * End tournament's round by delegating to the current state.
     * @param TournamentRound $round
     * @return void
     */
    public function endRound(TournamentRound $round): void
    {
        $this->state->endRound($round);
    }

    /**
     * Initialize current State
     *
     * @return InitialState|InProgressState|FinishedState
     */
    public function initializeState(): InProgressState|FinishedState|InitialState
    {
        switch ($this->stateString) {
            case TournamentStatesEnum::INITIAL:
                $this->state = new InitialState();
                break;
            case TournamentStatesEnum::IN_PROGRESS:
                $this->state = new InProgressState();
                break;
            case TournamentStatesEnum::FINISHED:
                $this->state = new FinishedState();
                break;
        }

        $this->state->setTournament($this);
        return $this->state;
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

    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): self
    {
        if (!$this->teams->contains($team)) {
            $this->teams->add($team);
        }

        return $this;
    }

    public function removeTeam(Team $team): self
    {
        $this->teams->removeElement($team);

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

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function isStarted(): ?bool
    {
        return $this->isStarted;
    }

    public function setIsStarted(bool $isStarted): self
    {
        $this->isStarted = $isStarted;

        return $this;
    }

    public function isFinished(): ?bool
    {
        return $this->isFinished;
    }

    public function setIsFinished(bool $isFinished): self
    {
        $this->isFinished = $isFinished;

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

    public function getMaxTeams(): int
    {
        return $this->maxTeams;
    }

    public function setMaxTeams(int $maxTeams): self
    {
        $this->maxTeams = $maxTeams;
        return $this;
    }

    public function hasLoserBracket(): bool
    {
        return $this->hasLoserBracket;
    }

    public function setHasLoserBracket(bool $hasLoserBracket): self
    {
        $this->hasLoserBracket = $hasLoserBracket;
        return $this;
    }

    public function getCashPrice(): ?string
    {
        return $this->cashPrice;
    }

    public function setCashPrice(string $cashPrice): self
    {
        $this->cashPrice = $cashPrice;

        return $this;
    }

    public function getTournamentBrackets(): Collection
    {
        return $this->tournamentBrackets;
    }

    public function addTournamentBracket(TournamentBracket $tournamentBracket): self
    {
        if (!$this->tournamentBrackets->contains($tournamentBracket)) {
            $this->tournamentBrackets->add($tournamentBracket);
            $tournamentBracket->setTournament($this);
        }

        return $this;
    }

    public function getRanking(): ?TournamentRanking
    {
        return $this->ranking;
    }

    public function setRanking(?TournamentRanking $ranking): self
    {
        $this->ranking = $ranking;
        return $this;
    }

    /**
     * @return TournamentStateInterface
     */
    public function getState(): TournamentStateInterface
    {
        return $this->state;
    }

    public function getStateString(): TournamentStatesEnum
    {
        return $this->stateString;
    }

}
