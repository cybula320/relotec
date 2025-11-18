<?php

namespace App\Helpers;

use App\Models\Oferta;
use Carbon\Carbon;

class OfferNumberHelper
{
    public static function generate(): string
    {
        $now = Carbon::now();

        $month = (int) $now->format('m');
        $year = (int) $now->format('Y');

        $lastOffer = Oferta::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($lastOffer) {
            [$numberPart] = explode('/', $lastOffer->numer, 2);
            $nextNumber = ((int) $numberPart) + 1;
        }

        // format: 1/10/2025
        return sprintf('%d/%d/%d', $nextNumber, $month, $year);
    }

    public static function generateCorrectionLetter(Oferta $parent): string
    {
        $existingLetters = Oferta::where('parent_oferta_id', $parent->id)
            ->whereNotNull('correction_letter')
            ->pluck('correction_letter')
            ->toArray();

        return self::nextLetter($existingLetters);
    }

    public static function buildCorrectionNumber(string $baseNumber, string $letter): string
    {
        // 1/10/2025 + A => 1A/10/2025
        [$first, $rest] = explode('/', $baseNumber, 2);

        return sprintf('%s%s/%s', $first, $letter, $rest);
    }

    protected static function nextLetter(array $existingLetters): string
    {
        if (empty($existingLetters)) {
            return 'A';
        }

        $maxIndex = 0;
        foreach ($existingLetters as $letter) {
            $index = self::letterToIndex($letter);
            if ($index > $maxIndex) {
                $maxIndex = $index;
            }
        }

        return self::indexToLetter($maxIndex + 1);
    }

    protected static function letterToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $len = strlen($letters);
        $index = 0;

        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }

        return $index - 1;
    }

    protected static function indexToLetter(int $index): string
    {
        $index += 1;
        $letters = '';

        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letters = chr(ord('A') + $mod) . $letters;
            $index = (int) floor(($index - 1) / 26);
        }

        return $letters;
    }
}