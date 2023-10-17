<?php

namespace App\Tournament\Enum;

enum TournamentEnum: string
{
    case ONE = '1s';
    case TWO = '2s';
    case FOUR = '4s';
    case EIGHT = '8s';

    public static function getValidTypes(): array
    {
        return [
            self::ONE->value,
            self::TWO->value,
            self::FOUR->value,
            self::EIGHT->value,
        ];
    }

}