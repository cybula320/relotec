<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Oferta extends Model
{
    use HasFactory;

    protected $table = 'oferty';

    protected $fillable = [
        'numer', 'firma_id', 'handlowiec_id', 'waluta',
        'payment_terms_days', 'due_date', 'uwagi',
        'total_net', 'total_gross', 'status', 'converted_order_id',
    ];

    public function firma() { return $this->belongsTo(Firma::class); }
    public function handlowiec() { return $this->belongsTo(Handlowiec::class); }
    public function pozycje() { return $this->hasMany(OfertaPozycja::class); }

    public function recalcTotals(): void
    {
        $totals = $this->pozycje()->selectRaw('SUM(total_net) as net, SUM(total_gross) as gross')->first();
        $this->update([
            'total_net' => $totals->net ?? 0,
            'total_gross' => $totals->gross ?? 0,
        ]);
    }

    public static function generateNumber(): string
    {
        $year = now()->format('Y');
        $count = self::whereYear('created_at', now()->year)->count() + 1;
        return sprintf('OF-%s-%04d', $year, $count);
    }

    protected static function booted()
{
    static::creating(function ($oferta) {
        if (empty($oferta->numer)) {
            $oferta->numer = \App\Helpers\OfferNumberHelper::generate();
        }
    });
}

 

public function recalculateTotals(): void
{
    $totalNet = $this->pozycje()->sum('total_net');
    $totalGross = $this->pozycje()->sum('total_gross');

    $this->update([
        'total_net' => $totalNet,
        'total_gross' => $totalGross,
    ]);
}


}