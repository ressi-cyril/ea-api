<?php

namespace App\Model\Match\Dto;

class MatchDto
{
    public string $score;

    public function getScore(): string
    {
        return $this->score;
    }

    public function setScore(string $score): MatchDto
    {
        $this->score = $score;
        return $this;
    }

}