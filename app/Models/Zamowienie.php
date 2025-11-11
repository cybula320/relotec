<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zamowienie extends Model
{
    use HasFactory;

    protected $table = 'zamowienia';

    protected $fillable = [
        'numer', 'firma_id', 'handlowiec_id', 'waluta',
        'payment_terms_days', 'due_date', 'uwagi',
        'total_net', 'total_gross', 'status',
    ];

    public function firma() { return $this->belongsTo(Firma::class); }
    public function handlowiec() { return $this->belongsTo(Handlowiec::class); }
    public function pozycje() { return $this->hasMany(ZamowieniePozycja::class); }

    public static function generateNumber(): string
    {
        $year = now()->format('Y');
        $count = self::whereYear('created_at', now()->year)->count() + 1;
        return sprintf('ZAM-%s-%04d', $year, $count);
    }

    public function recalcTotals(): void
    {
        $totals = $this->pozycje()->selectRaw('SUM(total_net) as net, SUM(total_gross) as gross')->first();
        $this->update([
            'total_net' => $totals->net ?? 0,
            'total_gross' => $totals->gross ?? 0,
        ]);
    }
}