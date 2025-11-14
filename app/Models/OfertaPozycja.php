<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class OfertaPozycja extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'oferta_pozycje';

    protected $fillable = [
        'oferta_id',
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

    public function oferta() { return $this->belongsTo(Oferta::class); }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('Oferta Pozycja')
            ->logOnlyDirty(false)
            ->submitEmptyLogs();
    }
    protected static function booted()
    {
        static::saved(function ($pozycja) {
            if ($pozycja->oferta) {
                $pozycja->oferta->recalculateTotals();
    
                // 🔥 Wyślij event do Livewire
                if (method_exists($pozycja->oferta, 'emitTo')) {
                    $pozycja->oferta->emitTo(
                        'filament.resources.ofertas.pages.edit-oferta',
                        'refreshSummary'
                    );
                }
            }
        });
    }
}