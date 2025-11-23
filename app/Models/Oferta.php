<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class Oferta extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'oferty';

    protected $fillable = [
        'numer', 'tytul', 'firma_id', 'handlowiec_id', 'user_id', 'waluta',
        'payment_terms_days', 'due_date', 'uwagi',
        'total_net', 'total_gross', 'status', 'converted_order_id', 'payment_method_id', 
        'parent_oferta_id', 'correction_letter',
    ];

    public function firma() { return $this->belongsTo(Firma::class); }
    public function handlowiec() { return $this->belongsTo(Handlowiec::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function pozycje() { return $this->hasMany(OfertaPozycja::class); }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('Oferta')
            ->logOnlyDirty(false)
            ->submitEmptyLogs();
    }

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

    public function paymentMethod()
    {
        return $this->belongsTo(\App\Models\PaymentMethod::class);
    }

    public function parentOferta()
    {
        return $this->belongsTo(self::class, 'parent_oferta_id');
    }

    public function corrections()
    {
        return $this->hasMany(self::class, 'parent_oferta_id');
    }

    public function isCorrection(): bool
    {
        return ! is_null($this->parent_oferta_id);
    }
}