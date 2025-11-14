<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    //

    use SoftDeletes;

    protected $fillable = [
        'nazwa',
        'opis',
        'termin',
        'aktywny',
    ];

    public function firmy()
     {
         return $this->belongsToMany(Firma::class, 'firma_payment_method');
      }



}
