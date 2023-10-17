<?php

namespace App\Tournament\Enum;

enum TournamentStatesEnum: string
{
    case INITIAL = 'initial';
    case IN_PROGRESS = 'in_progress';
    case FINISHED = 'finished';

}