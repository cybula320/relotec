<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Firma extends Model
{
    //
    use HasFactory, SoftDeletes,    LogsActivity;

    protected $table = 'firmy';

    protected $fillable = [
        'nazwa',
        'nip',
        'email',
        'telefon',
        'adres',
        'miasto',
        'uwagi',
        'payment_method_id',
    ];

    public function handlowcy()
    {
        return $this->hasMany(Handlowiec::class, 'firma_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('Firma')
            ->logOnlyDirty(false)
            ->submitEmptyLogs();
    }
    
    public function paymentMethod()
    {
        return $this->belongsTo(\App\Models\PaymentMethod::class);
    }
}
