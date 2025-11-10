<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Firma extends Model
{
    //
    use HasFactory, SoftDeletes;

    protected $table = 'firmy';

    protected $fillable = [
        'nazwa',
        'nip',
        'email',
        'telefon',
        'adres',
        'miasto',
        'uwagi',
    ];

    public function handlowcy()
    {
        return $this->hasMany(Handlowiec::class, 'firma_id');
    }

 
}
