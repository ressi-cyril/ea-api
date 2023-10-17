<?php

namespace App\Entity\Tournament;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TournamentMatchFinal extends TournamentMatch
{
    #[ORM\Column]
    private ?bool $requiresReplay = false;

    #[ORM\Column]
    private ?bool $isGrandFinal = false;

    public function getIsGrandFinal(): ?bool
    {
        return $this->isGrandFinal;
    }

    public function setIsGrandFinal(?bool $isGrandFinal): self
    {
        $this->isGrandFinal = $isGrandFinal;
        return $this;
    }

    public function isRequiresReplay(): bool
    {
        return $this->requiresReplay;
    }

    public function setRequiresReplay(bool $requiresReplay): self
    {
        $this->requiresReplay = $requiresReplay;
        return $this;
    }

}
