<?php

namespace App\Service;

class UtilsService
{
    /**
     * @param array $matchesData
     * @return string
     */
    public function getLastLetter(array $matchesData): string
    {
        $lastLetter = 'A';

        foreach ($matchesData as $matchData) {
            if (isset($matchData['from_game'])) {
                $lastLetter = $matchData['from_game'];
            }
        }

        return $lastLetter;
    }

    /**
     * @param $letter
     * @return string
     */
    public function getNextLetter($letter): string
    {
        if ($letter === 'Z') {
            return 'AA';
        }

        $length = strlen($letter);
        $carry = 1;

        for ($i = $length - 1; $i >= 0; $i--) {
            $char = $letter[$i];
            $newChar = chr(ord($char) + $carry);

            if ($newChar > 'Z') {
                $carry = 1;
                $newChar = 'A';
            } else {
                $carry = 0;
            }

            $letter[$i] = $newChar;

            if ($carry === 0) {
                break;
            }
        }

        if ($carry === 1) {
            $letter = 'A' . $letter;
        }

        return $letter;
    }

    /**
     * Validates the given score format.
     *
     * @param string $score The score string to validate.
     * @return bool Returns true if valid, false otherwise.
     */
    public static function isValidScoreFormat(string $score): bool
    {
        // Valid format:  3-0.    a digit, a hyphen, a digit
        return preg_match('/^[0-9]-[0-9]$/', $score) === 1;
    }

    public static function isPowerOfTwo($n): bool
    {
        if ($n <= 0) {
            return false;
        }

        $logBaseTwo = log($n, 2);
        return floor($logBaseTwo) == $logBaseTwo;
    }
}