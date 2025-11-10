<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Handlowiec extends Model
{
    //
    use HasFactory, SoftDeletes;

    protected $table = 'handlowcy';

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
}
