<?php

namespace App\Tournament\Dto;

use JMS\Serializer\Annotation as Serializer;

class TournamentDto
{
    public string $name;

    public int $points;

    public int $maxTeams;

    public bool $hasLoserBracket;

    public string $type;

    public string $cashPrice;

    public \DateTime $startAt;

    /**
     * @Serializer\Type("array")
     * @var array|null
     */
    public ?array $pointsByTier = null;

    public function getPointsByTier(): array
    {
        return $this->pointsByTier;
    }

    public function setPointsByTier(array $pointsByTier): TournamentDto
    {
        $this->pointsByTier = $pointsByTier;
        return $this;
    }

}