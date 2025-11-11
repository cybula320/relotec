<?php

namespace App\Helpers;

use App\Models\Oferta;
use Carbon\Carbon;

class OfferNumberHelper
{
    public static function generate(): string
    {
        $now = Carbon::now();

        // miesiąc i rok
        $month = $now->format('m');
        $year = $now->format('Y');

        // znajdź ostatnią ofertę z bieżącego miesiąca
        $lastOffer = Oferta::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        // jeśli nie ma, zaczynamy od 1
        $nextNumber = $lastOffer ? ((int) explode('/', $lastOffer->numer)[0] + 1) : 1;

        // sformatuj numer: 001/11/2025
        return sprintf('%03d/%02d/%04d', $nextNumber, $month, $year);
    }
}