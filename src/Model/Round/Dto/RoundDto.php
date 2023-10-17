<?php

namespace App\Model\Round\Dto;

class RoundDto
{
    /**
     * @var array
     */
    public array $infos = [];

    public int $bestOf;

    public function getInfos(): array
    {
        return $this->infos;
    }

    public function setInfos(array $infos): RoundDto
    {
        $this->infos = $infos;
        return $this;
    }

    public function getBestOf(): int
    {
        return $this->bestOf;
    }

    public function setBestOf(int $bestOf): RoundDto
    {
        $this->bestOf = $bestOf;
        return $this;
    }

}