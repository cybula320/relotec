<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class Handlowiec extends Model
{
    //
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'handlowcy, LogsActivity';

    protected $fillable = [
        'firma_id',
        'imie',
        'nazwisko',
        'email',
        'telefon',
    ];

    public function firma()
    {
        return $this->belongsTo(Firma::class, 'firma_id');
    }

  
    public function getFullNameAttribute()
    {
        return "{$this->imie} {$this->nazwisko}";
    }


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('Handlowcy')
            ->logOnlyDirty(false)
            ->submitEmptyLogs();
    }
}
