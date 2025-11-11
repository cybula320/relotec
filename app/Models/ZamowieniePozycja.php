<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZamowieniePozycja extends Model
{
    use HasFactory;

    protected $table = 'zamowienie_pozycje';

    protected $fillable = [
        'zamowienie_id',
        'nazwa',
        'opis',
        'ilosc',
        'unit_price_net',
        'unit_price_gross',
        'vat_rate',
        'zdjecie',
        'uwagi',
        'total_net',
        'total_gross',
    ];

    public function zamowienie() { return $this->belongsTo(Zamowienie::class); }

    protected static function booted()
    {
        static::saving(function ($item) {
            $item->total_net = $item->ilosc * $item->unit_price_net;
            $item->total_gross = $item->total_net * (1 + ($item->vat_rate / 100));
        });

        static::saved(fn ($item) => $item->zamowienie?->recalcTotals());
        static::deleted(fn ($item) => $item->zamowienie?->recalcTotals());
    }
}